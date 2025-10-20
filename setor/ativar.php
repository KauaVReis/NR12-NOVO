<?php session_start(); ?>

<?php
// Incluindo a conexão com o banco de dados
include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador']);

try {
    // Definindo o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Falha na conexão: " . $e->getMessage());
}

// Verifica se o ID do Setor foi enviado via GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta de atualização para ativar o Setor
    $sql = "UPDATE setor SET setor_status = 'Ativo' WHERE idsetor = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Executa a consulta e redireciona para consulta.php
    if ($stmt->execute()) {
        // Redireciona automaticamente sem exibir alertas
        header("Location: consulta.php");
        exit;
    } else {
        echo "Erro ao Ativar o Setor.";
    }
} else {
    echo "ID do Setor não fornecido.";
}

// Fechar a conexão (opcional com PDO)
$pdo = null;
?>
