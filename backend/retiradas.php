<?php
require_once 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $acao = $_GET['acao'] ?? 'historico';

    // 1. CHECAR TRAVA DE 21 DIAS E PEDIDOS PENDENTES
    if ($acao === 'checar_trava') {
        $funcionario_id = intval($_GET['funcionario_id'] ?? 0);
        $epi_nome = trim($_GET['epi_nome'] ?? '');

        try {
            // A) Verifica se já existe um pedido PENDENTE para este mesmo EPI
            $stmtPend = $pdo->prepare("SELECT id FROM solicitacoes WHERE funcionario_id = ? AND epi_nome = ? AND status = 'pendente'");
            $stmtPend->execute([$funcionario_id, $epi_nome]);
            if ($stmtPend->fetch()) {
                echo json_encode([
                    "pendente_existente" => true,
                    "bloqueado" => true,
                    "mensagem" => "Você já possui uma solicitação PENDENTE para este EPI. Aguarde a avaliação do gestor."
                ]);
                exit;
            }

            // B) Verifica se houve retirada nos últimos 21 dias (3 semanas)
            $stmt = $pdo->prepare("SELECT data_hora FROM retiradas WHERE funcionario_id = ? AND epi_nome = ? AND data_hora >= DATE_SUB(NOW(), INTERVAL 21 DAY) ORDER BY data_hora DESC LIMIT 1");
            $stmt->execute([$funcionario_id, $epi_nome]);
            $ultima = $stmt->fetch();

            if ($ultima) {
                echo json_encode([
                    "pendente_existente" => false,
                    "bloqueado" => true,
                    "ultima_data" => date('d/m/Y H:i', strtotime($ultima['data_hora'])),
                    "mensagem" => "EPI retirado a menos de 21 dias. Justificativa obrigatória."
                ]);
            } else {
                echo json_encode(["pendente_existente" => false, "bloqueado" => false]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
        }
        exit;
    }

    // 2. DADOS PARA O GRÁFICO DE BARRAS
    if ($acao === 'grafico') {
        try {
            $stmt = $pdo->query("SELECT DATE_FORMAT(data_hora, '%m/%Y') as mes, epi_nome, COUNT(*) as total FROM retiradas GROUP BY mes, epi_nome ORDER BY data_hora ASC");
            echo json_encode($stmt->fetchAll());
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
        }
        exit;
    }

    // 3. HISTÓRICO DE RETIRADAS COM FILTROS
    try {
        $funcionario_id = $_GET['funcionario_id'] ?? '';
        $epi_nome = $_GET['epi_nome'] ?? '';

        $query = "SELECT * FROM retiradas WHERE 1=1";
        $params = [];

        if (!empty($funcionario_id)) {
            $query .= " AND funcionario_id = ?";
            $params[] = $funcionario_id;
        }

        if (!empty($epi_nome)) {
            $query .= " AND epi_nome = ?";
            $params[] = $epi_nome;
        }

        $query .= " ORDER BY data_hora DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        echo json_encode($stmt->fetchAll());
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
    exit;
}
?>