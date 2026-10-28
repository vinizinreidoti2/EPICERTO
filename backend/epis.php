<?php
require_once 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $somenteAtivos = isset($_GET['somente_ativos']) && $_GET['somente_ativos'] == '1';
        $sql = $somenteAtivos ? "SELECT * FROM epis WHERE status = 'ativo' ORDER BY nome ASC" : "SELECT * FROM epis ORDER BY id DESC";
        $stmt = $pdo->query($sql);
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

    // ATIVAR / INATIVAR EPI (NÃO EXCLUI DO BANCO)
    if ($acao === 'alternar_status') {
        $id = intval($data['id'] ?? 0);
        $novoStatus = $data['novo_status'] === 'inativo' ? 'inativo' : 'ativo';
        try {
            $stmt = $pdo->prepare("UPDATE epis SET status = ? WHERE id = ?");
            $stmt->execute([$novoStatus, $id]);
            echo json_encode(["status" => "sucesso", "mensagem" => "Status alterado com sucesso!"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
        }
        exit;
    }

    // AJUSTAR ESTOQUE (+1 / -1)
    if ($acao === 'ajustar_estoque') {
        $id = intval($data['id'] ?? 0);
        $delta = intval($data['delta'] ?? 0);
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

    // CADASTRAR EPI
    if (empty($data['nome']) || !isset($data['quantidade']) || !isset($data['estoque_minimo'])) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => "Preencha todos os campos do EPI."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO epis (nome, quantidade, estoque_minimo, status) VALUES (?, ?, ?, 'ativo')");
        $stmt->execute([trim($data['nome']), intval($data['quantidade']), intval($data['estoque_minimo'])]);
        echo json_encode(["status" => "sucesso", "mensagem" => "EPI cadastrado com sucesso!"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
    exit;
}
?>