<?php
// =====================================================
// Lótus Studio — api/agendamento.php
// =====================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'lotus_studio');
define('DB_USER', 'root');
define('DB_PASS', '');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'mensagem' => 'Método não permitido.'
    ]);
    exit;
}

// Lê o JSON
$body = json_decode(file_get_contents('php://input'), true);

if (!$body) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'mensagem' => 'Payload inválido.'
    ]);
    exit;
}

// Campos obrigatórios
$campos = [
    'nome_cliente',
    'unidade',
    'data',
    'hora',
    'servicos',
    'pagamento',
    'total'
];

foreach ($campos as $campo) {
    if (!isset($body[$campo]) || $body[$campo] === '') {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'mensagem' => "Campo obrigatório ausente: $campo"
        ]);
        exit;
    }
}

try {

    // Conexão
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

    // Dados
    $nome      = trim($body['nome_cliente']);
    $unidade   = trim($body['unidade']);
    $data      = trim($body['data']);
    $hora      = trim($body['hora']);
    $servicos  = implode(', ', (array)$body['servicos']);
    $pagamento = trim($body['pagamento']);
    $cupom     = isset($body['cupom']) ? trim($body['cupom']) : null;
    $total     = (float)$body['total'];

    $protocolo = 'LTS-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));

    // Inserção
    $stmt = $pdo->prepare("
        INSERT INTO agendamentos
        (
            protocolo,
            nome_cliente,
            unidade,
            data_agendamento,
            hora_agendamento,
            servicos,
            pagamento,
            cupom,
            total
        )
        VALUES
        (
            :protocolo,
            :nome,
            :unidade,
            :data,
            :hora,
            :servicos,
            :pagamento,
            :cupom,
            :total
        )
    ");

    $stmt->execute([
        ':protocolo' => $protocolo,
        ':nome'      => $nome,
        ':unidade'   => $unidade,
        ':data'      => $data,
        ':hora'      => $hora,
        ':servicos'  => $servicos,
        ':pagamento' => $pagamento,
        ':cupom'     => $cupom,
        ':total'     => $total
    ]);

    echo json_encode([
        'success'   => true,
        'protocolo' => $protocolo,
        'mensagem'  => 'Agendamento confirmado com sucesso!'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'mensagem' => 'Erro ao salvar agendamento.',
        'erro' => $e->getMessage()
    ]);
}