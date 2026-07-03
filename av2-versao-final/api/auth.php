<?php
// ============================================================
//  LÓTUS STUDIO — api/auth.php
//  Registro, login, logout e verificacao de sessao do cliente
// ============================================================

// Sessao precisa ser iniciada ANTES de qualquer output
session_start([
    'cookie_httponly' => true,
    'cookie_samesite'  => 'Lax'
]);

header('Content-Type: application/json; charset=utf-8');
// Como o front-end roda no mesmo dominio, nao usamos '*' aqui porque
// requisicoes com credenciais (cookies de sessao) exigem uma origem explicita.
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

try {
    $pdo = new PDO('mysql:host=localhost;dbname=lotus_studio;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensagem' => 'Erro de conexao com o banco de dados.']);
    exit;
}

function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function usuarioPublico($row) {
    return [
        'id'    => (int) $row['id'],
        'nome'  => $row['nome'],
        'email' => $row['email'],
        'admin' => (bool) $row['admin']
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

// ---- GET: verificar sessao atual ----
if ($method === 'GET' && ($_GET['action'] ?? '') === 'me') {
    if (!empty($_SESSION['usuario_id'])) {
        $stmt = $pdo->prepare('SELECT id, nome, email, admin FROM usuarios WHERE id = ?');
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt->fetch();
        if ($usuario) {
            respond(['success' => true, 'logado' => true, 'usuario' => usuarioPublico($usuario)]);
        }
    }
    respond(['success' => true, 'logado' => false]);
}

if ($method !== 'POST') {
    respond(['success' => false, 'mensagem' => 'Metodo nao permitido.'], 405);
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    respond(['success' => false, 'mensagem' => 'Payload invalido.'], 400);
}

$action = $body['action'] ?? '';

// ---- POST: cadastro ----
if ($action === 'register') {
    $nome  = trim($body['nome'] ?? '');
    $email = strtolower(trim($body['email'] ?? ''));
    $senha = (string) ($body['senha'] ?? '');

    if ($nome === '' || $email === '' || $senha === '') {
        respond(['success' => false, 'mensagem' => 'Preencha nome, e-mail e senha.'], 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(['success' => false, 'mensagem' => 'E-mail invalido.'], 422);
    }
    if (strlen($senha) < 6) {
        respond(['success' => false, 'mensagem' => 'A senha deve ter pelo menos 6 caracteres.'], 422);
    }

    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        respond(['success' => false, 'mensagem' => 'Ja existe uma conta com este e-mail.'], 409);
    }

    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)')
        ->execute([$nome, $email, $hash]);

    $novoId = (int) $pdo->lastInsertId();

    // login automatico apos cadastro
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $novoId;

    respond(['success' => true, 'mensagem' => 'Cadastro realizado com sucesso!', 'usuario' => [
        'id' => $novoId, 'nome' => $nome, 'email' => $email, 'admin' => false
    ]]);
}

// ---- POST: login ----
if ($action === 'login') {
    $email = strtolower(trim($body['email'] ?? ''));
    $senha = (string) ($body['senha'] ?? '');

    if ($email === '' || $senha === '') {
        respond(['success' => false, 'mensagem' => 'Informe e-mail e senha.'], 422);
    }

    $stmt = $pdo->prepare('SELECT id, nome, email, senha_hash, admin FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
        respond(['success' => false, 'mensagem' => 'E-mail ou senha incorretos.'], 401);
    }

    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuario['id'];

    respond(['success' => true, 'mensagem' => 'Login realizado com sucesso!', 'usuario' => usuarioPublico($usuario)]);
}

// ---- POST: logout ----
if ($action === 'logout') {
    $_SESSION = [];
    session_destroy();
    respond(['success' => true, 'mensagem' => 'Sessao encerrada.']);
}

respond(['success' => false, 'mensagem' => 'Acao nao reconhecida.'], 405);
