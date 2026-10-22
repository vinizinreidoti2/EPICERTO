<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $usuarioInput = trim($data['usuario'] ?? '');
    $senhaInput = trim($data['senha'] ?? '');
    $perfilInput = trim($data['perfil'] ?? '');

    if (empty($usuarioInput) || empty($senhaInput)) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => "Preencha o usuário e a senha."]);
        exit;
    }

    try {
        // Busca por e-mail ou matrícula
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE (email = ? OR matricula = ?) AND perfil = ?");
        $stmt->execute([$usuarioInput, $usuarioInput, $perfilInput]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            // Verifica senha criptografada (password_verify) OU aceita texto puro temporariamente
            $senhaValida = password_verify($senhaInput, $usuario['senha']) || ($senhaInput === $usuario['senha']);

            if ($senhaValida) {
                // Se a senha estava em texto puro, atualiza automaticamente para criptografada no banco
                if ($senhaInput === $usuario['senha']) {
                    $novaHash = password_hash($senhaInput, PASSWORD_DEFAULT);
                    $updateStmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                    $updateStmt->execute([$novaHash, $usuario['id']]);
                }

                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Login realizado com sucesso!",
                    "usuario" => [
                        "id" => $usuario['id'],
                        "nome" => $usuario['nome'],
                        "email" => $usuario['email'],
                        "matricula" => $usuario['matricula'],
                        "perfil" => $usuario['perfil'],
                        "primeiro_acesso" => (int)$usuario['primeiro_acesso']
                    ]
                ]);
                exit;
            }
        }

        http_response_code(401);
        echo json_encode(["status" => "erro", "mensagem" => "Credenciais inválidas ou perfil incorreto."]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
}
?>