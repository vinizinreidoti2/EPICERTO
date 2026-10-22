<?php
require_once 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM epis ORDER BY id DESC");
        echo json_encode($stmt->fetchAll());
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $acao = $data['acao'] ?? 'cadastrar';

    // AJUSTAR ESTOQUE (+ / -)
    if ($acao === 'ajustar_estoque') {
        $id = intval($data['id'] ?? 0);
        $delta = intval($data['delta'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "ID inválido."]);
            exit;
        }
        try {
            $stmt = $pdo->prepare("UPDATE epis SET quantidade = GREATEST(0, quantidade + ?) WHERE id = ?");
            $stmt->execute([$delta, $id]);
            echo json_encode(["status" => "sucesso", "mensagem" => "Estoque atualizado!"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
        }
        exit;
    }

    // EXCLUIR EPI
    if ($acao === 'excluir') {
        $id = intval($data['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM epis WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(["status" => "sucesso", "mensagem" => "EPI excluído com sucesso!"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
        }
        exit;
    }

    // CADASTRAR NOVO EPI
    if (empty($data['nome']) || !isset($data['quantidade']) || !isset($data['estoque_minimo'])) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => "Preencha todos os campos do EPI."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO epis (nome, quantidade, estoque_minimo) VALUES (?, ?, ?)");
        $stmt->execute([trim($data['nome']), intval($data['quantidade']), intval($data['estoque_minimo'])]);
        echo json_encode(["status" => "sucesso", "mensagem" => "EPI cadastrado com sucesso!"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => "Erro ao cadastrar EPI: " . $e->getMessage()]);
    }
    exit;
}
?>