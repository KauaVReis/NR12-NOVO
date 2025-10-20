<?php
include '../conexao.php';

if (isset($_GET['id']) && isset($_GET['acao'])) {
    $id = $_GET['id'];
    $acao = $_GET['acao'];

    // Define o novo status com base na ação
    $novoStatus = (strtolower($acao) === 'ativar') ? 'Ativo' : 'Inativo';


    // Prepara a query para atualizar o status
    $query = "UPDATE manutencao SET manutencao_status = :novoStatus WHERE idmanutencao = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':novoStatus', $novoStatus);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Executa a query e verifica se foi bem-sucedida
    if ($stmt->execute()) {
        echo "Status atualizado para $novoStatus com sucesso!";
    } else {
        echo "Erro ao atualizar o status.";
    }
} else {
    echo "ID ou ação inválidos.";
}
?>
<meta http-equiv="refresh" content="0; URL='tabela.php'">