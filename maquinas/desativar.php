<?php
// Incluindo a conexão com o banco de dados
include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador','Professor']);

try {
    // Definindo o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Falha na conexão: " . $e->getMessage());
}

// Verifica se o ID do tipo de maquina foi enviado via GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta de atualização para desativar o tipo de maquina
    $sql = "UPDATE maquina SET maquina_status = 'Inativo' WHERE idmaquina = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Executa a consulta e verifica se foi bem-sucedida
    if ($stmt->execute()) {
        echo "<script>alert('Máquina desativada com sucesso!'); window.location.href='consulta.php';</script>";
    } else {
        echo "Erro ao desativar Máquina.";
    }
} else {
    echo "ID da Máquina não fornecido.";
}

// Fechar a conexão (opcional com PDO)
$pdo = null;
?>
