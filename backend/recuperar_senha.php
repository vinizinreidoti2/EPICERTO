<?php
header('Content-Type: application/json; charset=utf-8');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("
        SELECT * FROM chamados_senha 
        WHERE status = 'pendente' 
        ORDER BY data_solicitacao DESC
    ");
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $acao = $data['acao'] ?? 'solicitar';

    if ($acao === 'solicitar') {
        $identificador = trim($data['identificador'] ?? '');
        $observacao = trim($data['observacao'] ?? '');

        $stmt = $pdo->prepare("SELECT id, nome, setor FROM usuarios WHERE email = ? OR matricula = ? LIMIT 1");
        $stmt->execute([$identificador, $identificador]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(["status" => "erro", "mensagem" => "E-mail ou matrícula não encontrado no sistema."]);
            exit;
        }

        $ins = $pdo->prepare("
            INSERT INTO chamados_senha (usuario_id, identificador, usuario_nome, setor, observacao) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $ins->execute([$usuario['id'], $identificador, $usuario['nome'], $usuario['setor'], $observacao]);
        $protocolo = 'TI-' . str_pad($pdo->lastInsertId(), 4, '0', STR_PAD_LEFT);

        echo json_encode([
            "status" => "ok",
            "protocolo" => $protocolo,
            "nome" => $usuario['nome'],
            "mensagem" => "Chamado aberto com sucesso para o suporte de TI / Gestão."
        ]);
        exit;
    }

    if ($acao === 'resetar') {
        $chamadoId = (int)$data['chamado_id'];
        $usuarioId = (int)$data['usuario_id'];

        $updUser = $pdo->prepare("
            UPDATE usuarios 
            SET senha = '123456', primeiro_acesso = 1, termo_aceito = 0, data_aceite_termo = NULL 
            WHERE id = ?
        ");
        $updUser->execute([$usuarioId]);

        $updChamado = $pdo->prepare("UPDATE chamados_senha SET status = 'resolvido' WHERE id = ?");
        $updChamado->execute([$chamadoId]);

        echo json_encode(["status" => "ok", "mensagem" => "Senha redefinida para 123456 com sucesso."]);
        exit;
    }
}
?>