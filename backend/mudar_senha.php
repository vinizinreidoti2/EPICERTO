<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $id = intval($data['id'] ?? 0);
    $novaSenha = trim($data['nova_senha'] ?? '');

    if ($id <= 0 || empty($novaSenha)) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => "Dados inválidos para alteração de senha."]);
        exit;
    }

    try {
        // Criptografa a nova senha com password_hash
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

        // Atualiza a senha e marca primeiro_acesso = 0
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ?, primeiro_acesso = 0 WHERE id = ?");
        $stmt->execute([$senhaHash, $id]);

        echo json_encode(["status" => "sucesso", "mensagem" => "Senha alterada com sucesso!"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
}
?>