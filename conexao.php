<?php
// Dados de conexão
$host = 'localhost';
$dbname = 'nr12';
$username = 'root';
$password = '';
$port = '3308';
date_default_timezone_set('America/Sao_Paulo');

try {
    // A porta foi movida para dentro da string DSN
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";

    $pdo = new PDO($dsn, $username, $password);

    // Definindo o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // echo "Conectado com sucesso!"; 
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>