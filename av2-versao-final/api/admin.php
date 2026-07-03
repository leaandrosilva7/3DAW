<?php
// ============================================================
//  LÓTUS STUDIO — api/admin.php
// ============================================================

session_start([
    'cookie_httponly' => true,
    'cookie_samesite'  => 'Lax'
]);

header('Content-Type: application/json; charset=utf-8');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$pdo = new PDO('mysql:host=localhost;dbname=lotus_studio;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

// ---- guarda de acesso: precisa estar logado E ser admin ----
if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'mensagem' => 'Não autenticado.']);
    exit;
}

$stmtAdmin = $pdo->prepare('SELECT admin FROM usuarios WHERE id = ?');
$stmtAdmin->execute([$_SESSION['usuario_id']]);
$usuarioAtual = $stmtAdmin->fetch();

if (!$usuarioAtual || !$usuarioAtual['admin']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'mensagem' => 'Acesso restrito a administradores.']);
    exit;
}

// ---- GET: listar todos os agendamentos (com unidade e servicos "montados") ----
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'listar') {
    $agendamentos = $pdo->query('
        SELECT a.*, u.nome AS unidade_nome
        FROM agendamentos a
        JOIN unidades u ON u.id = a.unidade_id
        ORDER BY a.criado_em DESC
    ')->fetchAll();

    // busca os servicos de todos os agendamentos de uma vez só,
    // e depois agrupa em memoria por agendamento_id (evita N+1 queries)
    $itens = $pdo->query('
        SELECT ags.agendamento_id, s.nome, ags.preco_cobrado
        FROM agendamento_servicos ags
        JOIN servicos s ON s.id = ags.servico_id
    ')->fetchAll();

    $servicosPorAgendamento = [];
    foreach ($itens as $item) {
        $servicosPorAgendamento[$item['agendamento_id']][] = $item['nome'];
    }

    foreach ($agendamentos as &$ag) {
        $ag['servicos'] = implode(', ', $servicosPorAgendamento[$ag['id']] ?? []);
    }
    unset($ag);

    echo json_encode(['success' => true, 'agendamentos' => $agendamentos]);
    exit;
}

// ---- POST: editar ou excluir ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    // EXCLUIR
    if (($body['action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM agendamentos WHERE id = ?')->execute([$body['id']]);
        echo json_encode(['success' => true, 'mensagem' => 'Excluído com sucesso.']);
        exit;
    }

    // EDITAR
    if (($body['action'] ?? '') === 'edit') {
        $idsServicos = array_values(array_unique((array) ($body['servicos'] ?? [])));

        if (empty($idsServicos)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'mensagem' => 'Selecione ao menos um servico.']);
            exit;
        }

        // busca preco atual dos servicos escolhidos 
        $placeholders = implode(',', array_fill(0, count($idsServicos), '?'));
        $stmt = $pdo->prepare("SELECT id, preco FROM servicos WHERE id IN ($placeholders)");
        $stmt->execute($idsServicos);
        $servicosEncontrados = $stmt->fetchAll();

        if (count($servicosEncontrados) !== count($idsServicos)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'mensagem' => 'Um ou mais servicos sao invalidos.']);
            exit;
        }

        $total = array_reduce($servicosEncontrados, function ($soma, $s) {
            return $soma + (float) $s['preco'];
        }, 0.0);

        $pdo->beginTransaction();

        $pdo->prepare('
            UPDATE agendamentos SET
                nome_cliente     = ?,
                unidade_id       = ?,
                data_agendamento = ?,
                hora_agendamento = ?,
                pagamento        = ?,
                cupom            = ?,
                total            = ?
            WHERE id = ?
        ')->execute([
            trim($body['nome_cliente']),
            trim($body['unidade_id']),
            trim($body['data_agendamento']),
            trim($body['hora_agendamento']),
            trim($body['pagamento']),
            !empty($body['cupom']) ? trim($body['cupom']) : null,
            $total,
            (int) $body['id']
        ]);

        // apaga os itens antigos e insere os novos de novo.
        $pdo->prepare('DELETE FROM agendamento_servicos WHERE agendamento_id = ?')
            ->execute([(int) $body['id']]);

        $stmtItem = $pdo->prepare('
            INSERT INTO agendamento_servicos (agendamento_id, servico_id, preco_cobrado)
            VALUES (?, ?, ?)
        ');
        foreach ($servicosEncontrados as $s) {
            $stmtItem->execute([(int) $body['id'], $s['id'], $s['preco']]);
        }

        $pdo->commit();

        echo json_encode(['success' => true, 'mensagem' => 'Atualizado com sucesso.']);
        exit;
    }
}

http_response_code(405);
echo json_encode(['success' => false, 'mensagem' => 'Ação não reconhecida.']);
