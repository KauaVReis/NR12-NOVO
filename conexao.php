<?php
// Dados de conexão
$host = 'localhost';
$dbname = 'nr12';
$username = 'root';
$password = '';
date_default_timezone_set('America/Sao_Paulo');


try {
    // Criando a conexão PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Definindo o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Conexão bem-sucedida
} catch (PDOException $e) {
    // Caso haja erro na conexão
    die("Erro na conexão: " . $e->getMessage());
}
?>
