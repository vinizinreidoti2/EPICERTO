<?php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $funcionario = $_GET['funcionario'] ?? null;
        if ($funcionario) {
            $stmt = $pdo->prepare("SELECT * FROM retiradas WHERE funcionario_nome = ? ORDER BY data_hora DESC");
            $stmt->execute([$funcionario]);
        } else {
            $stmt = $pdo->query("SELECT * FROM retiradas ORDER BY data_hora DESC");
        }
        echo json_encode($stmt->fetchAll());
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO retiradas (funcionario_nome, epi_nome, metodo) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['funcionario_nome'],
            $data['epi_nome'],
            $data['metodo'] ?? 'Lançamento Manual'
        ]);

        $stmtEstoque = $pdo->prepare("UPDATE epis SET quantidade = GREATEST(0, quantidade - 1) WHERE nome = ?");
        $stmtEstoque->execute([$data['epi_nome']]);

        $pdo->commit();
        http_response_code(201);
        echo json_encode(["status" => "sucesso", "mensagem" => "Retirada gravada!"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
}
?>