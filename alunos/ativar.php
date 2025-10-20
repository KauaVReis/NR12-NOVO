<?php
// Incluindo a conexão com o banco de dados
include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador', 'Professor']);

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
    $sql = "UPDATE aluno SET aluno_status = 'Ativo' WHERE idaluno = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Executa a consulta e verifica se foi bem-sucedida
    if ($stmt->execute()) {
        echo "<script>alert('Aluno Ativado com sucesso!'); window.location.href='consulta.php';</script>";
    } else {
        echo "Erro ao Ativar o Aluno.";
    }
} else {
    echo "ID do tipo da aluno não fornecido.";
}

// Fechar a conexão (opcional com PDO)
$pdo = null;
?>
