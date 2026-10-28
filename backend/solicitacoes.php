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

    // AÇÕES DO GESTOR (APROVAR OU RECUSAR)
    if (isset($data['acao']) && $data['acao'] === 'atualizar_status') {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE solicitacoes SET status = ? WHERE id = ?");
            $stmt->execute([$data['status'], $data['id']]);

            if ($data['status'] === 'aprovado') {
                $stmtSol = $pdo->prepare("SELECT * FROM solicitacoes WHERE id = ?");
                $stmtSol->execute([$data['id']]);
                $solicitacao = $stmtSol->fetch();

                if ($solicitacao) {
                    $stmtEstoque = $pdo->prepare("UPDATE epis SET quantidade = GREATEST(0, quantidade - 1) WHERE nome = ?");
                    $stmtEstoque->execute([$solicitacao['epi_nome']]);

                    $stmtRetirada = $pdo->prepare("INSERT INTO retiradas (funcionario_id, funcionario_nome, epi_nome, justificativa) VALUES (?, ?, ?, ?)");
                    $stmtRetirada->execute([
                        $solicitacao['funcionario_id'],
                        $solicitacao['funcionario_nome'],
                        $solicitacao['epi_nome'],
                        $solicitacao['justificativa'] ?? 'Solicitação Regular'
                    ]);
                }
            }

            $pdo->commit();
            echo json_encode(["status" => "sucesso", "mensagem" => "Status atualizado!"]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
        }
        exit;
    }

    // NOVA SOLICITAÇÃO DO FUNCIONÁRIO (COM VALIDAÇÕES NO SERVIDOR)
    if (empty($data['funcionario_id']) || empty($data['funcionario_nome']) || empty($data['epi_nome']) || empty($data['motivo'])) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => "Preencha todos os campos requeridos."]);
        exit;
    }

    $funcId = intval($data['funcionario_id']);
    $epiNome = trim($data['epi_nome']);

    // 1. Bloqueia se já houver pedido PENDENTE para o mesmo EPI
    $stmtCheckPend = $pdo->prepare("SELECT id FROM solicitacoes WHERE funcionario_id = ? AND epi_nome = ? AND status = 'pendente'");
    $stmtCheckPend->execute([$funcId, $epiNome]);
    if ($stmtCheckPend->fetch()) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => "Você já possui um pedido PENDENTE para este EPI. Aguarde a avaliação do gestor."]);
        exit;
    }

    // 2. Exige justificativa se foi retirado há menos de 21 dias
    $stmtCheckTrava = $pdo->prepare("SELECT data_hora FROM retiradas WHERE funcionario_id = ? AND epi_nome = ? AND data_hora >= DATE_SUB(NOW(), INTERVAL 21 DAY) ORDER BY data_hora DESC LIMIT 1");
    $stmtCheckTrava->execute([$funcId, $epiNome]);
    if ($stmtCheckTrava->fetch()) {
        if (empty(trim($data['justificativa'] ?? ''))) {
            http_response_code(400);
            echo json_encode(["status" => "erro", "mensagem" => "Este EPI foi retirado a menos de 21 dias. É obrigatório escrever uma justificativa."]);
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO solicitacoes (funcionario_id, funcionario_nome, epi_nome, motivo, justificativa) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $funcId,
            trim($data['funcionario_nome']),
            $epiNome,
            trim($data['motivo']),
            !empty($data['justificativa']) ? trim($data['justificativa']) : null
        ]);
        
        echo json_encode(["status" => "sucesso", "mensagem" => "Solicitação registrada com sucesso!"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
    exit;
}
?>