<?php
session_start(); // Adicione esta linha se ainda não estiver no início do arquivo

include '../conexao.php'; // Verifique se o caminho está correto

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe os dados do formulário
    $id = $_POST['id'];
    $turma_nome = $_POST['turma_nome'];
    $turma_periodo = $_POST['turma_periodo'];
    $turma_inicio = $_POST['turma_inicio'];
    $turma_fim = $_POST['turma_fim'];
    $colaborador_id = $_POST['colaborador_id'];
    $curso_id = $_POST['curso_id'];

    // Prepara a consulta
    $sql = "UPDATE turmas SET turma_nome = :turma_nome, turma_periodo = :turma_periodo, turma_inicio = :turma_inicio, turma_fim = :turma_fim, curso_id = :curso_id, colaborador_id = :colaborador_id WHERE idturmas = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':turma_nome', $turma_nome);
        $stmt->bindParam(':turma_periodo', $turma_periodo);
        $stmt->bindParam(':turma_inicio', $turma_inicio);
        $stmt->bindParam(':turma_fim', $turma_fim);
        $stmt->bindParam(':colaborador_id', $colaborador_id);
        $stmt->bindParam(':curso_id', $curso_id);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Turma atualizada com sucesso!";
            $_SESSION['tipo_mensagem'] = "success"; // Altere isso se você tiver estilos diferentes para tipos de mensagens
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar a turma.";
            $_SESSION['tipo_mensagem'] = "error";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro: " . htmlspecialchars($e->getMessage());
        $_SESSION['tipo_mensagem'] = "error";
    }
}

// Redireciona de volta para a página de edição
header("Location: consulta.php");
exit();

?>
