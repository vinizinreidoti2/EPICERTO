<?php
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$usuario = $data['usuario'] ?? '';
$senha = $data['senha'] ?? '';
$perfil = $data['perfil'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT id, nome, email, matricula, perfil FROM usuarios WHERE (email = :usuario OR matricula = :usuario) AND senha = :senha AND perfil = :perfil");
    $stmt->execute([
        'usuario' => $usuario,
        'senha' => $senha,
        'perfil' => $perfil
    ]);
    
    $user = $stmt->fetch();

    if ($user) {
        echo json_encode(["status" => "sucesso", "usuario" => $user]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "erro", "mensagem" => "Credenciais inválidas ou perfil incorreto!"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
}
?>