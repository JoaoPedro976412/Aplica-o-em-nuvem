<?php
/**
 * Configurações do Sistema - Ambiente Docker
 * Trabalho Prático: Cloud/WEB/Docker
 * Gerencia conexões com MySQL para ambientes Docker e XAMPP
 */

// Configurações para ambiente Docker
function getDatabaseConfig() {
    // Verificar se estamos no Docker (variáveis de ambiente)
    $dockerHost = getenv('MYSQL_HOST');
    
    if ($dockerHost) {
        // Configurações Docker - USA AS VARIÁVEIS DO RAILWAY
        return [
            'host' => $dockerHost ?: 'mysql.railway.internal',
            'user' => getenv('MYSQL_USER') ?: 'root',
            'password' => getenv('MYSQL_PASSWORD') ?: '',
            'database' => getenv('MYSQL_DATABASE') ?: 'railway',
            'environment' => 'DOCKER'
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
    
    // Tentativa de conexão direta com o banco
    $conn = new mysqli(
        $config['host'],
        $config['user'], 
        $config['password'],
        $config['database']
    );
    
    // Se conexão bem-sucedida
    if (!$conn->connect_error) {
        logSystemInfo("Conexão MySQL estabelecida com sucesso - Ambiente: " . $config['environment']);
        return $conn;
    }
    
    // Se não conseguir conectar, tentar criar o banco
    logSystemInfo("Tentando criar banco de dados...");
    
    // Conexão sem database específico
    $conn_temp = new mysqli(
        $config['host'],
        $config['user'], 
        $config['password']
    );
    
    if ($conn_temp->connect_error) {
        $error_msg = "Erro na conexão com o MySQL: " . $conn_temp->connect_error;
        logSystemInfo($error_msg);
        
        // Tentativa de fallback para localhost se estiver no Docker
        if ($config['environment'] === 'DOCKER') {
            logSystemInfo("Tentando fallback para localhost...");
            $config['host'] = 'localhost';
            $config['user'] = 'root';
            $config['password'] = '';
            
            $conn_fallback = new mysqli(
                $config['host'],
                $config['user'], 
                $config['password']
            );
            
            if (!$conn_fallback->connect_error) {
                logSystemInfo("Fallback para localhost bem-sucedido");
                return initializeDatabase($conn_fallback, $config['database']);
            }
        }
        
        die("<div style='padding: 20px; background: #f8d7da; color: #721c24; border-radius: 5px; margin: 20px;'>
                <h3>❌ Erro de Conexão com o Banco de Dados</h3>
                <p><strong>Erro:</strong> " . $conn_temp->connect_error . "</p>
                <p><strong>Ambiente:</strong> " . $config['environment'] . "</p>
                <p><strong>Host:</strong> " . $config['host'] . "</p>
                <p>Verifique se o MySQL está rodando e as credenciais estão corretas.</p>
            </div>");
    }
    
    return initializeDatabase($conn_temp, $config['database']);
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
        'docker_running' => !empty(getenv('MYSQL_HOST')),
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
        'environment' => getDatabaseConfig()['environment']
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
