<?php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT id, matricula, nome, codigo_cracha, setor, perfil FROM usuarios WHERE perfil = 'funcionario' ORDER BY id DESC");
        echo json_encode($stmt->fetchAll());
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data['nome']) || empty($data['matricula'])) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => "Preencha todos os campos."]);
        exit;
    }

    $email = strtolower($data['matricula']) . "@empresa.com";

    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, matricula, codigo_cracha, setor, perfil) VALUES (?, ?, '123456', ?, ?, ?, 'funcionario')");
        $stmt->execute([
            $data['nome'],
            $email,
            $data['matricula'],
            $data['codigo_cracha'] ?? '',
            $data['setor'] ?? ''
        ]);

        http_response_code(201);
        echo json_encode(["status" => "sucesso", "mensagem" => "Funcionário cadastrado!"]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
}
?>