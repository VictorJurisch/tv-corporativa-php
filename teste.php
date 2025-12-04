<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>🔍 Diagnóstico do Sistema</h2>";

// Teste 1: Config
echo "<h3>1.  Testando Config... </h3>";
try {
    require_once __DIR__ . '/api/config.php';
    echo "✅ Config OK<br>";
} catch (Exception $e) {
    echo "❌ Erro no config: " . $e->getMessage() . "<br>";
    die();
}

// Teste 2: Database
echo "<h3>2. Testando Database...</h3>";
try {
    require_once __DIR__ . '/includes/database.php';
    echo "✅ Database class OK<br>";
} catch (Exception $e) {
    echo "❌ Erro no database: " . $e->getMessage() . "<br>";
    die();
}

// Teste 3: Functions
echo "<h3>3. Testando Functions...</h3>";
try {
    require_once __DIR__ . '/includes/functions.php';
    echo "✅ Functions OK<br>";
} catch (Exception $e) {
    echo "❌ Erro no functions: " . $e->getMessage() . "<br>";
    die();
}

// Teste 4: Conexão
echo "<h3>4.  Testando Conexão com Banco...</h3>";
try {
    $db = Database::getInstance();
    echo "✅ Conexão OK<br>";
} catch (Exception $e) {
    echo "❌ Erro de conexão: " .  $e->getMessage() . "<br>";
    die();
}

// Teste 5: Tabela tv_config
echo "<h3>5.  Testando Tabela tv_config... </h3>";
try {
    $result = $db->fetch("SELECT * FROM tv_config LIMIT 1");
    if ($result) {
        echo "✅ Tabela tv_config existe e tem dados<br>";
    } else {
        echo "⚠️ Tabela tv_config existe mas está vazia<br>";
    }
} catch (Exception $e) {
    echo "❌ Tabela tv_config NÃO existe: " . $e->getMessage() . "<br>";
    echo "<br><strong>👉 Execute o arquivo database_complementar. sql no phpMyAdmin!</strong><br>";
}

// Teste 6: Coluna tipo em conteudos
echo "<h3>6. Testando Coluna 'tipo' em conteudos...</h3>";
try {
    $result = $db->fetch("SHOW COLUMNS FROM conteudos LIKE 'tipo'");
    if ($result) {
        echo "✅ Coluna 'tipo' existe<br>";
    } else {
        echo "❌ Coluna 'tipo' NÃO existe<br>";
        echo "<br><strong>👉 Execute o arquivo database_complementar.sql no phpMyAdmin!</strong><br>";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

echo "<h3>✅ Diagnóstico concluído!</h3>";