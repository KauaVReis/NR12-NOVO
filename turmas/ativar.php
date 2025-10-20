<?php
// Incluindo a conexão com o banco de dados
include '../conexao.php';

try {
    // Definindo o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Falha na conexão: " . $e->getMessage());
}

// Verifica se o ID da aluno foi enviado via GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta de atualização para desativar a aluno
    $sql = "UPDATE turmas SET turmas_status = 'Ativo' WHERE idturmas = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Executa a consulta e verifica se foi bem-sucedida
    if ($stmt->execute()) {
        echo "<script>alert('Turma Ativada com sucesso!'); window.location.href='consulta.php';</script>";
    } else {
        echo "Erro ao Ativar o Turma.";
    }
} else {
    echo "ID do tipo da Turma não fornecido.";
}

// Fechar a conexão (opcional com PDO)
$pdo = null;
?>
