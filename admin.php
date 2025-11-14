<?php
require_once 'config-docker.php';

$conn = createConnection();

// Estatísticas do banco
$total_usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch()['total'];
$tabelas = $conn->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .card { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #007bff; color: white; }
    </style>
</head>
<body>
    <h1> Sistema de Cadastro</h1>
    
    <div class="card">
        <h3> Estatísticas</h3>
        <p><strong>Total de usuários:</strong> <?php echo $total_usuarios; ?></p>
        <p><strong>Arquivo do banco:</strong> <?php echo realpath('database.sqlite'); ?></p>
    </div>

    <div class="card">
        <h3> Tabelas no Banco</h3>
        <?php foreach($tabelas as $tabela): ?>
            <p> <?php echo $tabela['name']; ?></p>
        <?php endforeach; ?>
    </div>

    <h3> Usuários Cadastrados</h3>
    <?php if ($total_usuarios > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                    <th>Cidade</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $usuarios = $conn->query("SELECT * FROM usuarios ORDER BY data_cadastro DESC");
                while($user = $usuarios->fetch()): 
                ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['nome']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['telefone']); ?></td>
                    <td><?php echo htmlspecialchars($user['cidade']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p> Nenhum usuário cadastrado.</p>
    <?php endif; ?>

    <br>
    <a href="index.php" style="padding: 10px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;">↩️ Voltar para o Sistema</a>
</body>
</html>
<?php
$conn = null;
?>
