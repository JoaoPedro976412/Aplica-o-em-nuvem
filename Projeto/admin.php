<?php
require_once 'config-docker.php';
$conn = createConnection();
$result = $conn->query("SELECT * FROM usuarios ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Banco de Dados</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="database-container">
        <div class="database-header">
            <h1>📊 Banco de Dados - Sistema Cadastro</h1>
            <p class="subtitle">Painel de Administração</p>
        </div>
        
        <div class="stats-card">
            📈 Registros: <strong><?php echo $result->num_rows; ?></strong>
        </div>
        
        <div class="database-table-container">
            <?php if ($result->num_rows > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Data de Cadastro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><span class="id-badge"><?php echo $row['id']; ?></span></td>
                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($row['data_cadastro'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <p>📭 Nenhum usuário cadastrado no momento</p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="database-actions">
            <a href="index.php" class="btn-admin btn-admin-secondary">
                ⬅ Voltar para o Cadastro
            </a>
            <a href="http://localhost:8081" target="_blank" class="btn-admin">
                🗃️ Acessar phpMyAdmin
            </a>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>