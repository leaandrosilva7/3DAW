<?php
// ============================================================
//  LÓTUS STUDIO 
// ============================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET')     { http_response_code(405); echo json_encode(['success' => false, 'mensagem' => 'Método não permitido.']); exit; }

try {
    $pdo = new PDO('mysql:host=localhost;dbname=lotus_studio;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $unidades = $pdo->query('SELECT id, nome, endereco, nota, reviews, img FROM unidades ORDER BY nome')->fetchAll();
    $servicos = $pdo->query('SELECT id, nome, duracao, preco, img FROM servicos ORDER BY nome')->fetchAll();

    // PDO retorna DECIMAL como string — converte pra numero antes de mandar pro JS
    foreach ($unidades as &$u) { $u['nota'] = $u['nota'] !== null ? (float) $u['nota'] : null; }
    unset($u);
    foreach ($servicos as &$s) { $s['preco'] = (float) $s['preco']; }
    unset($s);

    echo json_encode(['success' => true, 'unidades' => $unidades, 'servicos' => $servicos]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensagem' => 'Erro ao carregar catálogo.']);
}
