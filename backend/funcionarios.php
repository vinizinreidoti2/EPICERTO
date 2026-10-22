<?php
require_once 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT id, matricula, nome, email, codigo_cracha, setor, perfil, primeiro_acesso FROM usuarios ORDER BY id DESC");
        echo json_encode($stmt->fetchAll());
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $acao = $data['acao'] ?? 'cadastrar';

    // EXCLUIR USUÁRIO
    if ($acao === 'excluir') {
        $id = intval($data['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(["status" => "sucesso", "mensagem" => "Usuário excluído com sucesso!"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["status" => "erro", "mensagem" => $e->getMessage()]);
        }
        exit;
    }

    // CADASTRAR USUÁRIO
    if (empty($data['nome']) || empty($data['matricula']) || empty($data['setor'])) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => "Preencha Nome, Matrícula e Setor."]);
        exit;
    }

    $nome = trim($data['nome']);
    
    if (empty($data['email'])) {
        $limpo = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $nome)));
        $partesNome = explode(' ', $limpo);
        $primeiroNome = $partesNome[0];
        $ultimoNome = count($partesNome) > 1 ? end($partesNome) : '';
        $emailGerado = $ultimoNome ? "{$primeiroNome}.{$ultimoNome}@empresa.com" : "{$primeiroNome}@empresa.com";
    } else {
        $emailGerado = trim($data['email']);
    }

    $perfil = $data['perfil'] ?? 'funcionario';
    $senhaPadrao = '123456';
    $senhaHash = password_hash($senhaPadrao, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, matricula, codigo_cracha, setor, perfil, primeiro_acesso) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([
            $nome,
            $emailGerado,
            $senhaHash,
            $data['matricula'],
            $data['codigo_cracha'] ?? '',
            $data['setor'],
            $perfil
        ]);

        http_response_code(201);
        echo json_encode([
            "status" => "sucesso", 
            "mensagem" => "Novo usuário cadastrado! E-mail gerado: {$emailGerado}"
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["status" => "erro", "mensagem" => "Erro ao cadastrar: " . $e->getMessage()]);
    }
    exit;
}
?>