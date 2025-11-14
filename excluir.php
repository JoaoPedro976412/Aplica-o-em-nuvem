<?php
require_once 'config-docker.php';

if ($_POST && isset($_POST['id'])) {
    $conn = createConnection();
    
    $id = intval($_POST['id']);
    
    // Verificar se o ID é válido
    if ($id <= 0) {
        header("Location: index.php?error=" . urlencode("ID inválido!"));
        exit;
    }
    
    // Verificar se o usuário existe
    $check_user = $conn->query("SELECT id, nome FROM usuarios WHERE id = $id");
    
    if ($check_user && $check_user->num_rows > 0) {
        $user_data = $check_user->fetch_assoc();
        $user_name = $user_data['nome'];
        
        $sql = "DELETE FROM usuarios WHERE id = $id";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: index.php?success=" . urlencode("Usuário '{$user_name}' excluído com sucesso!"));
        } else {
            header("Location: index.php?error=" . urlencode("Erro ao excluir: " . $conn->error));
        }
    } else {
        header("Location: index.php?error=" . urlencode("Usuário não encontrado!"));
    }
    
    $conn->close();
} else {
    header("Location: index.php");
    exit;
}
?>