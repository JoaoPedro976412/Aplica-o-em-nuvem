<?php
require_once 'config-docker.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    
    if (!empty($id)) {
        try {
            $conn = createConnection();
            
            // Excluir usuário
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            
            header('Location: index.php?success=Usuário excluído com sucesso!');
            
        } catch (Exception $e) {
            header('Location: index.php?error=Erro ao excluir usuário: ' . $e->getMessage());
        }
    } else {
        header('Location: index.php?error=ID do usuário não especificado');
    }
    
    exit;
}

// Se não for POST, redireciona para index
header('Location: index.php');
?>
