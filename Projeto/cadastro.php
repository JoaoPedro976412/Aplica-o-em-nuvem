<?php
require_once 'config-docker.php';

if ($_POST) {
    $conn = createConnection();
    
    // Coletar dados do formulário
    $nome = $conn->real_escape_string(trim($_POST['nome']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $telefone = $conn->real_escape_string(trim($_POST['telefone']));
    $cidade = $conn->real_escape_string(trim($_POST['cidade']));
    
    // Validações
    if (empty($nome) || empty($email)) {
        header("Location: index.php?error=" . urlencode("Nome e e-mail são obrigatórios!"));
        exit;
    }
    
    if (strlen($nome) < 2) {
        header("Location: index.php?error=" . urlencode("Nome deve ter pelo menos 2 caracteres!"));
        exit;
    }
    
    // Validar e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.php?error=" . urlencode("E-mail inválido!"));
        exit;
    }
    
    // Verificar se e-mail já existe
    $check_email = $conn->query("SELECT id FROM usuarios WHERE email = '$email'");
    if ($check_email && $check_email->num_rows > 0) {
        header("Location: index.php?error=" . urlencode("Este e-mail já está cadastrado!"));
        exit;
    }
    
    // Formatar telefone (remover caracteres não numéricos)
    $telefone = preg_replace('/\D/', '', $telefone);
    if (!empty($telefone) && strlen($telefone) < 10) {
        header("Location: index.php?error=" . urlencode("Telefone inválido!"));
        exit;
    }
    
    // Inserir no banco
    $sql = "INSERT INTO usuarios (nome, email, telefone, cidade) VALUES ('$nome', '$email', '$telefone', '$cidade')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: index.php?success=" . urlencode("Usuário cadastrado com sucesso!"));
    } else {
        header("Location: index.php?error=" . urlencode("Erro ao cadastrar: " . $conn->error));
    }
    
    $conn->close();
} else {
    header("Location: index.php");
    exit;
}
?>