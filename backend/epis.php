<?php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM epis ORDER BY id DESC");
        echo json_encode($stmt->fetchAll());
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['nome']) || !isset($data['quantidade'])) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => "Preencha o nome e a quantidade."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO epis (nome, quantidade, estoque_minimo) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantidade = quantidade + VALUES(quantidade), estoque_minimo = VALUES(estoque_minimo)");
        $stmt->execute([
            $data['nome'],
            intval($data['quantidade']),
            intval($data['estoque_minimo'] ?? 10)
        ]);

        echo json_encode(["status" => "sucesso", "mensagem" => "Estoque atualizado!"]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
}
?>