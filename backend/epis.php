<?php
header('Content-Type: application/json; charset=utf-8');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Para o funcionário: retorna apenas EPIs ATIVOS e com CA VÁLIDO (conformidade NR-6)
    if (isset($_GET['somente_ativos']) && $_GET['somente_ativos'] == '1') {
        $stmt = $pdo->query("
            SELECT * FROM epis 
            WHERE status = 'ativo' 
              AND (validade_ca IS NULL OR validade_ca >= CURDATE())
            ORDER BY nome ASC
        ");
    } else {
        // Para o gestor: retorna todos os EPIs com status do CA
        $stmt = $pdo->query("SELECT * FROM epis ORDER BY nome ASC");
    }
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $acao = $data['acao'] ?? 'cadastrar';

    if ($acao === 'alternar_status') {
        $stmt = $pdo->prepare("UPDATE epis SET status = ? WHERE id = ?");
        $stmt->execute([$data['novo_status'], $data['id']]);
        echo json_encode(["status" => "ok", "mensagem" => "Status atualizado."]);
        exit;
    }

    if ($acao === 'ajustar_estoque') {
        $stmt = $pdo->prepare("UPDATE epis SET quantidade = GREATEST(0, quantidade + ?) WHERE id = ?");
        $stmt->execute([(int)$data['delta'], $data['id']]);
        echo json_encode(["status" => "ok", "mensagem" => "Estoque ajustado."]);
        exit;
    }

    if ($acao === 'atualizar_ca') {
        $stmt = $pdo->prepare("UPDATE epis SET numero_ca = ?, validade_ca = ? WHERE id = ?");
        $stmt->execute([$data['numero_ca'], $data['validade_ca'], $data['id']]);
        echo json_encode(["status" => "ok", "mensagem" => "CA atualizado com sucesso."]);
        exit;
    }

    // Cadastro de novo EPI com CA e validade
    try {
        $stmt = $pdo->prepare("
            INSERT INTO epis (nome, numero_ca, validade_ca, quantidade, estoque_minimo, status) 
            VALUES (?, ?, ?, ?, ?, 'ativo')
        ");
        $stmt->execute([
            $data['nome'],
            $data['numero_ca'],
            $data['validade_ca'],
            (int)$data['quantidade'],
            (int)$data['estoque_minimo']
        ]);
        echo json_encode(["status" => "ok", "mensagem" => "EPI cadastrado com sucesso."]);
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => "Erro ao cadastrar EPI (verifique se o nome já existe)."]);
    }
    exit;
}
?>