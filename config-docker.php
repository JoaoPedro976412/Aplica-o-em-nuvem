<?php
/**
 * Configurações do Sistema - Ambiente Docker
 * Trabalho Prático: Cloud/WEB/Docker
 * Gerencia conexões com MySQL para ambientes Railway e XAMPP
 */

// Configurações para ambiente Railway
function getDatabaseConfig() {
    // Tenta TODAS as possíveis variáveis do Railway
    $host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'mysql.railway.internal';
    $user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
    $password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
    $database = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'railway';
    
    // FORÇA conexão Railway se detectar que está no Railway
    if (getenv('RAILWAY_PUBLIC_DOMAIN') || getenv('MYSQLHOST')) {
        return [
            'host' => $host,
            'user' => $user,
            'password' => $password,
            'database' => $database,
            'environment' => 'RAILWAY'
        ];
    } else {
        // Configurações XAMPP (desenvolvimento)
        return [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'database' => 'sistema_cadastro',
            'environment' => 'XAMPP'
        ];
    }
}

// Função para criar conexão
function createConnection() {
    $config = getDatabaseConfig();
    
    // PRIMEIRA TENTATIVA: Conectar com root (pode falhar se senha mudou)
    $conn = new mysqli(
        $config['host'],
        $config['user'], 
        $config['password'],
        $config['database']
    );
    
    // Se conexão bem-sucedida
    if (!$conn->connect_error) {
        logSystemInfo("Conexão MySQL estabelecida com sucesso - Ambiente: " . $config['environment']);
        
        // Tenta criar usuário personalizado para evitar problemas futuros
        createCustomUser($conn);
        
        return $conn;
    }
    
    // SE FALHOU: Tentar descobrir a senha atual do root
    logSystemInfo("Falha na conexão com root, tentando alternativas...");
    
    // Tentar conectar sem senha (para desenvolvimento)
    $conn_fallback = new mysqli($config['host'], 'root', '');
    if (!$conn_fallback->connect_error) {
        logSystemInfo("Conectado sem senha - criando usuário personalizado");
        return initializeWithCustomUser($conn_fallback, $config['database']);
    }
    
    die("<div style='padding: 20px; background: #f8d7da; color: #721c24; border-radius: 5px; margin: 20px;'>
            <h3>❌ Erro de Conexão com o Banco de Dados</h3>
            <p><strong>Erro:</strong> " . $conn->connect_error . "</p>
            <p><strong>Ambiente:</strong> " . $config['environment'] . "</p>
            <p><strong>Dica:</strong> O Railway pode ter regenerado a senha do MySQL.</p>
            <p>Verifique a senha atual em: MySQL → Variables → MYSQLPASSWORD</p>
            <p>Senha tentada: " . (empty($config['password']) ? '(vazia)' : '***') . "</p>
        </div>");
}

// Função para criar usuário personalizado
function createCustomUser($connection) {
    $custom_user = 'app_user';
    $custom_password = 'SenhaFixa123456';
    
    // Tenta criar usuário (pode falhar se não tiver permissões)
    try {
        $connection->query("CREATE USER IF NOT EXISTS '$custom_user'@'%' IDENTIFIED BY '$custom_password'");
        $connection->query("GRANT ALL PRIVILEGES ON railway.* TO '$custom_user'@'%'");
        $connection->query("FLUSH PRIVILEGES");
        logSystemInfo("Usuário personalizado criado: $custom_user");
    } catch (Exception $e) {
        logSystemInfo("Não foi possível criar usuário personalizado: " . $e->getMessage());
    }
}

function initializeWithCustomUser($connection, $databaseName) {
    // Primeiro inicializa o banco
    $conn = initializeDatabase($connection, $databaseName);
    
    // Depois cria usuário personalizado
    createCustomUser($conn);
    
    return $conn;
}

// Função para inicializar o banco de dados
function initializeDatabase($connection, $databaseName) {
    // Criar database se não existir
    if (!$connection->query("CREATE DATABASE IF NOT EXISTS $databaseName")) {
        die("Erro ao criar banco de dados: " . $connection->error);
    }
    
    // Selecionar o banco
    if (!$connection->select_db($databaseName)) {
        die("Erro ao selecionar banco de dados: " . $connection->error);
    }
    
    // Criar tabela se não existir
    $createTableSQL = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        telefone VARCHAR(20),
        cidade VARCHAR(50),
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$connection->query($createTableSQL)) {
        die("Erro ao criar tabela: " . $connection->error);
    }
    
    logSystemInfo("Banco de dados inicializado com sucesso: $databaseName");
    return $connection;
}

// Função para log do sistema
function logSystemInfo($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    
    // Log para arquivo (opcional)
    if (is_writable('.')) {
        file_put_contents('system.log', $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    // Log para output do PHP (útil para debug)
    error_log($message);
}

// Função para obter informações do ambiente
function getEnvironmentInfo() {
    $config = getDatabaseConfig();
    
    return [
        'environment' => $config['environment'],
        'mysql_host' => $config['host'],
        'database' => $config['database'],
        'railway_running' => !empty(getenv('MYSQLHOST')),
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'
    ];
}

// Função para testar conexão (útil para debug)
function testDatabaseConnection() {
    try {
        $conn = createConnection();
        
        if ($conn && $conn->ping()) {
            $result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
            $row = $result->fetch_assoc();
            
            return [
                'status' => 'success',
                'message' => 'Conexão estabelecida com sucesso',
                'total_usuarios' => $row['total'],
                'environment' => getDatabaseConfig()['environment']
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage(),
            'environment' => getDatabaseConfig()['environment']
        ];
    }
    
    return [
        'status' => 'unknown',
        'message' => 'Estado da conexão desconhecido',
        'environment' => getDatabaseConfig()['environment'
    ];
}

// Inicialização do sistema
logSystemInfo("Sistema de Cadastro iniciado");

// Debug mode (opcional - desative em produção)
if (isset($_GET['debug']) && $_GET['debug'] === 'db') {
    header('Content-Type: application/json');
    echo json_encode(testDatabaseConnection(), JSON_PRETTY_PRINT);
    exit;
}
?>
