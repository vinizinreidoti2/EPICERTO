<?php
header('Content-Type: application/json; charset=utf-8');
require 'db.php';

$funcionarioId = isset($_GET['funcionario_id']) ? (int)$_GET['funcionario_id'] : 0;

if ($funcionarioId <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "erro", "mensagem" => "ID do funcionário inválido."]);
    exit;
}

// 1. Busca os dados cadastrais do funcionário
$stmtUser = $pdo->prepare("
    SELECT id, nome, email, matricula, codigo_cracha, setor, perfil 
    FROM usuarios 
    WHERE id = ?
");
$stmtUser->execute([$funcionarioId]);
$funcionario = $stmtUser->fetch();

if (!$funcionario) {
    http_response_code(404);
    echo json_encode(["status" => "erro", "mensagem" => "Funcionário não encontrado."]);
    exit;
}

// 2. Busca o histórico de retiradas cruzando com o CA atual do EPI
$stmtRet = $pdo->prepare("
    SELECT 
        r.id,
        r.epi_nome,
        COALESCE(e.numero_ca, 'Não informado') AS numero_ca,
        e.validade_ca,
        r.metodo,
        r.justificativa,
        r.data_hora
    FROM retiradas r
    LEFT JOIN epis e ON r.epi_nome = e.nome
    WHERE r.funcionario_id = ?
    ORDER BY r.data_hora DESC
");
$stmtRet->execute([$funcionarioId]);
$retiradas = $stmtRet->fetchAll();

echo json_encode([
    "status" => "ok",
    "funcionario" => $funcionario,
    "retiradas" => $retiradas,
    "gerado_em" => date('d/m/Y H:i:s')
]);
?>