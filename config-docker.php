<?php
/**
 * Configurações do Sistema - SQLite
 * Trabalho Prático: Cloud/WEB/Docker
 */

// Função para criar conexão SQLite
function createConnection() {
    try {
        // Caminho do banco SQLite
        $databaseFile = __DIR__ . '/database.sqlite';
        
        // Conectar ao SQLite
        $conn = new PDO("sqlite:" . $databaseFile);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Criar tabela se não existir
        createTable($conn);
        
        return $conn;
        
    } catch (Exception $e) {
        die("<div style='padding: 20px; background: #f8d7da; color: #721c24; border-radius: 5px; margin: 20px;'>
                <h3>❌ Erro de Banco de Dados</h3>
                <p><strong>Erro:</strong> " . $e->getMessage() . "</p>
                <p>SQLite não pôde ser inicializado.</p>
            </div>");
    }
}

// Função para criar tabela
function createTable($connection) {
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            telefone TEXT,
            cidade TEXT,
            data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ";
    
    $connection->exec($createTableSQL);
}

// Função adaptada para SQLite (usada no index.php)
function getConnection() {
    return createConnection();
}

// Função para log do sistema
function logSystemInfo($message) {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message");
}

// Inicialização do sistema
logSystemInfo("Sistema de Cadastro com SQLite iniciado");
?>
