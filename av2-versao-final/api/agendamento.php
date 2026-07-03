<?php
// ============================================================
//  LÓTUS STUDIO — api/agendamento.php
//  Recebe o agendamento enviado pelo JS e salva no banco.
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
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { http_response_code(405); echo json_encode(['success' => false, 'mensagem' => 'Método não permitido.']); exit; }

// Lê o JSON enviado pelo JS
$body = json_decode(file_get_contents('php://input'), true);

if (!$body) {
    http_response_code(400);
    echo json_encode(['success' => false, 'mensagem' => 'Payload inválido.']);
    exit;
}

// Campos obrigatórios
foreach (['nome_cliente', 'unidade', 'data', 'hora', 'servicos', 'pagamento'] as $campo) {
    if (empty($body[$campo])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'mensagem' => "Campo obrigatório ausente: $campo"]);
        exit;
    }
}

if (!is_array($body['servicos']) || count($body['servicos']) === 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'mensagem' => 'Selecione ao menos um servico.']);
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=lotus_studio;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // ---- valida a unidade contra o catálogo ----
    $stmt = $pdo->prepare('SELECT id FROM unidades WHERE id = ?');
    $stmt->execute([$body['unidade']]);
    if (!$stmt->fetch()) {
        http_response_code(422);
        echo json_encode(['success' => false, 'mensagem' => 'Unidade invalida.']);
        exit;
    }

    // ---- busca os servicos escolhidos no banco (nunca confia no preco vindo do cliente) ----
    $idsServicos = array_values(array_unique($body['servicos']));
    $placeholders = implode(',', array_fill(0, count($idsServicos), '?'));
    $stmt = $pdo->prepare("SELECT id, preco FROM servicos WHERE id IN ($placeholders)");
    $stmt->execute($idsServicos);
    $servicosEncontrados = $stmt->fetchAll();

    if (count($servicosEncontrados) !== count($idsServicos)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'mensagem' => 'Um ou mais servicos sao invalidos.']);
        exit;
    }

    // total sempre recalculado no servidor, a partir do preco atual no banco
    $total = array_reduce($servicosEncontrados, function ($soma, $s) {
        return $soma + (float) $s['preco'];
    }, 0.0);

    $protocolo = 'LOTUS-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
    $usuarioId = !empty($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;

    // ---- grava tudo dentro de uma transacao (agendamento + itens) ----
    $pdo->beginTransaction();

    $pdo->prepare('
        INSERT INTO agendamentos
            (usuario_id, protocolo, nome_cliente, unidade_id, data_agendamento, hora_agendamento, pagamento, cupom, total)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ')->execute([
        $usuarioId,
        $protocolo,
        trim($body['nome_cliente']),
        $body['unidade'],
        trim($body['data']),
        trim($body['hora']),
        trim($body['pagamento']),
        !empty($body['cupom']) ? trim($body['cupom']) : null,
        $total
    ]);

    $agendamentoId = (int) $pdo->lastInsertId();

    $stmtItem = $pdo->prepare('
        INSERT INTO agendamento_servicos (agendamento_id, servico_id, preco_cobrado)
        VALUES (?, ?, ?)
    ');
    foreach ($servicosEncontrados as $s) {
        $stmtItem->execute([$agendamentoId, $s['id'], $s['preco']]);
    }

    $pdo->commit();

    echo json_encode([
        'success'   => true,
        'protocolo' => $protocolo,
        'mensagem'  => 'Agendamento confirmado com sucesso!'
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'mensagem' => 'Erro ao salvar: ' . $e->getMessage()]);
}
