<?php
// ============================================================
//  LÓTUS STUDIO — API Unificada
//  Salve como: htdocs/lotus-studio/api/agendamento.php
//
//  POST (sem parâmetro)      → cria agendamento  ← usado pelo JS
//  GET  ?rota=unidades       → lista unidades
//  GET  ?rota=servicos       → lista serviços
//  GET  ?rota=pagamentos     → lista métodos de pagamento
//  GET  ?rota=agendamentos   → lista/consulta agendamentos
// ============================================================

declare(strict_types=1);

// ---- CORS ----
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ============================================================
//  CONFIGURAÇÃO DO BANCO — ajuste se necessário
// ============================================================
define('DB_HOST',    'localhost');
define('DB_USER',    'root');   // padrão XAMPP
define('DB_PASS',    '');       // padrão XAMPP (sem senha)
define('DB_NAME',    'lotus_studio');
define('DB_CHARSET', 'utf8mb4');

// ============================================================
//  HELPERS
// ============================================================

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function getJsonBody(): array {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) jsonResponse(['success' => false, 'mensagem' => 'Body JSON inválido.'], 400);
    return $data;
}

function gerarProtocolo(): string {
    return 'LOTUS-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
}

// ============================================================
//  ROTEAMENTO
//
//  POST sem ?rota= → cria agendamento (compatível com o JS)
//  GET  ?rota=XXX  → rotas de leitura
// ============================================================

$method = $_SERVER['REQUEST_METHOD'];
$rota   = $_GET['rota'] ?? '';

if ($method === 'POST' && $rota === '') {
    createAgendamento();
}

if ($method === 'GET') {
    match ($rota) {
        'unidades'     => getUnidades(),
        'servicos'     => getServicos(),
        'pagamentos'   => getPagamentos(),
        'agendamentos' => getAgendamentos(),
        default        => jsonResponse([
            'success'  => false,
            'mensagem' => "Rota GET '$rota' não encontrada.",
            'rotas'    => ['unidades', 'servicos', 'pagamentos', 'agendamentos'],
        ], 404),
    };
}

jsonResponse(['success' => false, 'mensagem' => 'Método não permitido.'], 405);

// ============================================================
//  GET → unidades
// ============================================================
function getUnidades(): void {
    try {
        $rows = getDB()->query('SELECT id, nome, endereco, nota, reviews, img FROM unidades ORDER BY nome')->fetchAll();
        foreach ($rows as &$r) $r['nota'] = (float) $r['nota'];
        jsonResponse(['success' => true, 'data' => $rows]);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'mensagem' => 'Erro: ' . $e->getMessage()], 500);
    }
}

// ============================================================
//  GET → servicos
// ============================================================
function getServicos(): void {
    try {
        $rows = getDB()->query('SELECT id, nome, duracao, preco, img FROM servicos ORDER BY nome')->fetchAll();
        foreach ($rows as &$r) $r['preco'] = (float) $r['preco'];
        jsonResponse(['success' => true, 'data' => $rows]);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'mensagem' => 'Erro: ' . $e->getMessage()], 500);
    }
}

// ============================================================
//  GET → pagamentos
// ============================================================
function getPagamentos(): void {
    try {
        $rows = getDB()->query('SELECT id, nome, classe, img FROM metodos_pagamento ORDER BY id')->fetchAll();
        jsonResponse(['success' => true, 'data' => $rows]);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'mensagem' => 'Erro: ' . $e->getMessage()], 500);
    }
}

// ============================================================
//  GET → agendamentos  (?protocolo= &cliente= &limite= &pagina=)
// ============================================================
function getAgendamentos(): void {
    try {
        $db        = getDB();
        $protocolo = $_GET['protocolo'] ?? null;
        $cliente   = $_GET['cliente']   ?? null;
        $limite    = max(1, min(100, (int) ($_GET['limite'] ?? 20)));
        $pagina    = max(1, (int) ($_GET['pagina'] ?? 1));
        $offset    = ($pagina - 1) * $limite;

        $where  = 'WHERE 1=1';
        $params = [];
        if ($protocolo) { $where .= ' AND a.protocolo = ?';       $params[] = $protocolo; }
        if ($cliente)   { $where .= ' AND a.nome_cliente LIKE ?'; $params[] = '%' . $cliente . '%'; }

        $sql = "
            SELECT a.id, a.protocolo, a.nome_cliente,
                   u.nome AS unidade, p.nome AS pagamento,
                   a.cupom, a.total, a.status, a.criado_em
            FROM agendamentos a
            JOIN unidades          u ON u.id = a.unidade_id
            JOIN metodos_pagamento p ON p.id = a.pagamento_id
            $where
            ORDER BY a.criado_em DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge($params, [$limite, $offset]));
        $rows = $stmt->fetchAll();

        $stmtSv = $db->prepare('
            SELECT s.id, s.nome, s.preco
            FROM agendamento_servicos asv
            JOIN servicos s ON s.id = asv.servico_id
            WHERE asv.agendamento_id = ?
        ');
        foreach ($rows as &$ag) {
            $ag['total'] = (float) $ag['total'];
            $stmtSv->execute([$ag['id']]);
            $sv = $stmtSv->fetchAll();
            foreach ($sv as &$s) $s['preco'] = (float) $s['preco'];
            $ag['servicos'] = $sv;
        }

        $stmtCnt = $db->prepare("SELECT COUNT(*) FROM agendamentos a $where");
        $stmtCnt->execute($params);
        $total = (int) $stmtCnt->fetchColumn();

        jsonResponse([
            'success' => true,
            'data'    => $rows,
            'meta'    => [
                'total'   => $total,
                'pagina'  => $pagina,
                'limite'  => $limite,
                'paginas' => (int) ceil($total / $limite),
            ],
        ]);
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'mensagem' => 'Erro: ' . $e->getMessage()], 500);
    }
}

// ============================================================
//  POST → cria agendamento
// ============================================================
function createAgendamento(): void {
    $body = getJsonBody();

    $nomeCliente = trim($body['nome_cliente'] ?? '');
    $nomeUnidade = trim($body['unidade']      ?? '');
    $nomePagto   = trim($body['pagamento']    ?? '');
    $servicos    = $body['servicos']          ?? [];
    $cupom       = !empty($body['cupom']) ? trim($body['cupom']) : null;
    $total       = isset($body['total']) ? (float) $body['total'] : 0.0;

    if ($nomeCliente === '') jsonResponse(['success' => false, 'mensagem' => 'Campo nome_cliente é obrigatório.'], 422);
    if ($nomeUnidade === '') jsonResponse(['success' => false, 'mensagem' => 'Campo unidade é obrigatório.'], 422);
    if ($nomePagto   === '') jsonResponse(['success' => false, 'mensagem' => 'Campo pagamento é obrigatório.'], 422);
    if (empty($servicos))   jsonResponse(['success' => false, 'mensagem' => 'Selecione ao menos um serviço.'], 422);

    try {
        $db = getDB();

        $stmtU = $db->prepare('SELECT id FROM unidades WHERE nome = ?');
        $stmtU->execute([$nomeUnidade]);
        $unidade = $stmtU->fetch();
        if (!$unidade) jsonResponse(['success' => false, 'mensagem' => "Unidade não encontrada: $nomeUnidade"], 422);

        $stmtP = $db->prepare('SELECT id FROM metodos_pagamento WHERE nome = ?');
        $stmtP->execute([$nomePagto]);
        $pagto = $stmtP->fetch();
        if (!$pagto) jsonResponse(['success' => false, 'mensagem' => "Pagamento não encontrado: $nomePagto"], 422);

        $stmtS      = $db->prepare('SELECT id FROM servicos WHERE nome = ?');
        $servicoIds = [];
        foreach ($servicos as $nomeServico) {
            $stmtS->execute([trim($nomeServico)]);
            $s = $stmtS->fetch();
            if (!$s) jsonResponse(['success' => false, 'mensagem' => "Serviço não encontrado: $nomeServico"], 422);
            $servicoIds[] = $s['id'];
        }

        $db->beginTransaction();

        $protocolo = null;
        for ($i = 0; $i < 5; $i++) {
            $t   = gerarProtocolo();
            $chk = $db->prepare('SELECT id FROM agendamentos WHERE protocolo = ?');
            $chk->execute([$t]);
            if (!$chk->fetch()) { $protocolo = $t; break; }
        }
        if (!$protocolo) {
            $db->rollBack();
            jsonResponse(['success' => false, 'mensagem' => 'Erro ao gerar protocolo. Tente novamente.'], 500);
        }

        $db->prepare('INSERT INTO agendamentos (protocolo, nome_cliente, unidade_id, pagamento_id, cupom, total) VALUES (?,?,?,?,?,?)')
           ->execute([$protocolo, $nomeCliente, $unidade['id'], $pagto['id'], $cupom, $total]);

        $agId  = (int) $db->lastInsertId();
        $insSv = $db->prepare('INSERT INTO agendamento_servicos (agendamento_id, servico_id) VALUES (?,?)');
        foreach ($servicoIds as $sid) $insSv->execute([$agId, $sid]);

        $db->commit();

        jsonResponse([
            'success'   => true,
            'protocolo' => $protocolo,
            'mensagem'  => 'Agendamento confirmado com sucesso!',
            'data'      => [
                'id'           => $agId,
                'nome_cliente' => $nomeCliente,
                'unidade'      => $nomeUnidade,
                'servicos'     => $servicos,
                'pagamento'    => $nomePagto,
                'cupom'        => $cupom,
                'total'        => $total,
                'criado_em'    => date('Y-m-d H:i:s'),
            ],
        ], 201);

    } catch (PDOException $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        jsonResponse(['success' => false, 'mensagem' => 'Erro no banco: ' . $e->getMessage()], 500);
    }
}