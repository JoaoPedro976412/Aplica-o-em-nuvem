<?php
// Incluir configurações
require_once 'config-docker.php';

// Criar conexão
$conn = createConnection();

// Buscar usuários
$result = $conn->query("SELECT * FROM usuarios ORDER BY data_cadastro DESC");

// Mensagens de sucesso/erro
$message = '';
if (isset($_GET['success'])) {
    $message = '<div class="success-message">✅ ' . htmlspecialchars($_GET['success']) . '</div>';
} elseif (isset($_GET['error'])) {
    $message = '<div class="error-message">❌ ' . htmlspecialchars($_GET['error']) . '</div>';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Cadastro - Trabalho Prático</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Sistema de Cadastro</h1>
            <p>Trabalho Prático - Cloud/WEB/Docker</p>
        </div>
        
        <div class="content">
            <!-- Formulário de Cadastro -->
            <div class="form-section">
                <h2>➕ Cadastrar Novo Usuário</h2>
                <?php echo $message; ?>
                <form action="cadastro.php" method="POST">
                    <div class="form-group">
                        <label for="nome">Nome Completo:</label>
                        <input type="text" id="nome" name="nome" placeholder="Digite o nome completo" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" placeholder="Digite o e-mail" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefone">Telefone:</label>
                        <input type="tel" id="telefone" name="telefone" placeholder="(11) 99999-9999">
                    </div>
                    
                    <div class="form-group">
                        <label for="cidade">Cidade:</label>
                        <input type="text" id="cidade" name="cidade" placeholder="Digite a cidade">
                    </div>
                    
                    <button type="submit" class="btn">📋 Cadastrar Usuário</button>
                </form>
            </div>
            
            <!-- Lista de Usuários -->
            <div class="list-section">
                <h2>👥 Usuários Cadastrados</h2>
                <?php if ($result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Telefone</th>
                                <th>Cidade</th>
                                <th>Data Cadastro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['telefone']); ?></td>
                                <td><?php echo htmlspecialchars($row['cidade']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['data_cadastro'])); ?></td>
                                <td class="actions">
                                    <form action="excluir.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">🗑️ Excluir</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-message">
                        <p>📭 Nenhum usuário cadastrado ainda.</p>
                        <p>Use o formulário ao lado para cadastrar o primeiro usuário!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer">
            <p>Trabalho Prático - Desenvolvido com PHP + MySQL + Docker + Cloud</p>
            <p>Ambiente: <?php echo getenv('MYSQL_HOST') ? 'Docker' : 'XAMPP'; ?></p>
        </div>
    </div>

    <script>
        // Adicionar máscara de telefone
        document.getElementById('telefone')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 2) {
                    value = '(' + value;
                } else if (value.length <= 7) {
                    value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
                } else {
                    value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 7) + '-' + value.substring(7, 11);
                }
            }
            e.target.value = value;
        });

        // Auto-focus no primeiro campo
        document.getElementById('nome')?.focus();
    </script>
</body>
</html>
<?php
$conn->close();
?>