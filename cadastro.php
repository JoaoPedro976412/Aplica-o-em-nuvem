<?php
require_once 'config-docker.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    
    // Validações básicas
    if (empty($nome) || empty($email)) {
        header('Location: index.php?error=Nome e e-mail são obrigatórios');
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: index.php?error=E-mail inválido');
        exit;
    }
    
    try {
        $conn = createConnection();
        
        // Inserir usuário
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone, cidade) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $telefone, $cidade]);
        
        header('Location: index.php?success=Usuário cadastrado com sucesso!');
        
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
            header('Location: index.php?error=E-mail já cadastrado');
        } else {
            header('Location: index.php?error=Erro ao cadastrar usuário: ' . $e->getMessage());
        }
    }
    
    exit;
}

// Se não for POST, redireciona para index
header('Location: index.php');
?>
