<?php
header('Content-Type: application/json; charset=utf-8');
require 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : 0;
    $stmt = $pdo->prepare("SELECT termo_aceito, data_aceite_termo FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    echo json_encode($user ?: ["termo_aceito" => 0]);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $usuarioId = isset($data['usuario_id']) ? (int)$_GET['usuario_id'] ?? (int)$data['usuario_id'] : 0;

    if ($usuarioId <= 0) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => "Usuário inválido."]);
        exit;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $pdo->prepare("
        UPDATE usuarios 
        SET termo_aceito = 1, data_aceite_termo = NOW(), ip_aceite = ? 
        WHERE id = ?
    ");
    $stmt->execute([$ip, $usuarioId]);

    echo json_encode([
        "status" => "ok",
        "mensagem" => "Termo NR-6 assinado digitalmente com sucesso.",
        "data_aceite" => date('d/m/Y H:i:s')
    ]);
    exit;
}
?>