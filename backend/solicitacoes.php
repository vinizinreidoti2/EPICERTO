<?php
require_once 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $funcionario_id = $_GET['funcionario_id'] ?? null;
        if ($funcionario_id) {
            $stmt = $pdo->prepare("SELECT * FROM solicitacoes WHERE funcionario_id = ? ORDER BY id DESC");
            $stmt->execute([$funcionario_id]);
        } else {
            $stmt = $pdo->query("SELECT * FROM solicitacoes ORDER BY id DESC");
        }
        echo json_encode($stmt->fetchAll());
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['acao']) && $data['acao'] === 'atualizar_status') {
        try {
            $stmt = $pdo->prepare("UPDATE solicitacoes SET status = ? WHERE id = ?");
            $stmt->execute([$data['status'], $data['id']]);
            echo json_encode(["status" => "sucesso", "mensagem" => "Status atualizado!"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
        }
        exit;
    }

    if (empty($data['funcionario_id']) || empty($data['funcionario_nome']) || empty($data['epi_nome']) || empty($data['motivo'])) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => "Preencha todos os campos da solicitação."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO solicitacoes (funcionario_id, funcionario_nome, epi_nome, motivo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['funcionario_id'], $data['funcionario_nome'], $data['epi_nome'], $data['motivo']]);
        
        echo json_encode(["status" => "sucesso", "mensagem" => "Solicitação enviada com sucesso!"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
    exit;
}
?>