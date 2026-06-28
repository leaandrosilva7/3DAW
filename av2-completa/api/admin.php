<?php
// =====================================================
//  Lotus Studio - api/admin.php
//  Listar, editar e excluir agendamentos
// =====================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'lotus_studio');
define('DB_USER', 'root');
define('DB_PASS', '');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensagem' => 'Erro de conexao.']);
    exit;
}

// --- GET: listar ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'listar') {
    $stmt = $pdo->query("SELECT * FROM agendamentos ORDER BY criado_em DESC");
    echo json_encode(['success' => true, 'agendamentos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

// --- POST: editar ou excluir ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    if (!$body || empty($body['action'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'mensagem' => 'Payload invalido.']);
        exit;
    }

    // EXCLUIR
    if ($body['action'] === 'delete') {
        if (empty($body['id'])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'mensagem' => 'ID ausente.']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = :id");
        $stmt->execute([':id' => (int)$body['id']]);
        echo json_encode(['success' => true, 'mensagem' => 'Excluido com sucesso.']);
        exit;
    }

    // EDITAR
    if ($body['action'] === 'edit') {
        $campos = ['id','nome_cliente','unidade','data_agendamento','hora_agendamento','servicos','pagamento','total'];
        foreach ($campos as $c) {
            if (!isset($body[$c]) || $body[$c] === '') {
                http_response_code(422);
                echo json_encode(['success' => false, 'mensagem' => "Campo ausente: $c"]);
                exit;
            }
        }

        $stmt = $pdo->prepare("
            UPDATE agendamentos SET
                nome_cliente     = :nome,
                unidade          = :unidade,
                data_agendamento = :data,
                hora_agendamento = :hora,
                servicos         = :servicos,
                pagamento        = :pagamento,
                cupom            = :cupom,
                total            = :total
            WHERE id = :id
        ");

        $stmt->execute([
            ':id'       => (int)$body['id'],
            ':nome'     => trim($body['nome_cliente']),
            ':unidade'  => trim($body['unidade']),
            ':data'     => trim($body['data_agendamento']),
            ':hora'     => trim($body['hora_agendamento']),
            ':servicos' => trim($body['servicos']),
            ':pagamento'=> trim($body['pagamento']),
            ':cupom'    => !empty($body['cupom']) ? trim($body['cupom']) : null,
            ':total'    => (float)$body['total'],
        ]);

        echo json_encode(['success' => true, 'mensagem' => 'Atualizado com sucesso.']);
        exit;
    }
}

http_response_code(405);
echo json_encode(['success' => false, 'mensagem' => 'Metodo nao permitido.']);