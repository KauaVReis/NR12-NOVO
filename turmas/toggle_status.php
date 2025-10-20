
<?php
include '../conexao.php';

try {
    $idRequisito = $_GET['id'];

    // Consulta o status atual
    $sql = "SELECT turmas_status FROM turmas WHERE idturmas = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $idRequisito]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $statusAtual = $row['turmas_status'];

    // Inverte o status
    $novoStatus = ($statusAtual === 'Inativo') ? 'Ativo' : 'Inativo';

    // Atualiza o status no banco de dados
    $sql = "UPDATE turmas SET turmas_status = :novoStatus WHERE idturmas = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['novoStatus' => $novoStatus, 'id' => $idRequisito]);

    header("Location: consulta.php"); // Redireciona para a página de lista

} catch (PDOException $e) {
    die("Erro ao atualizar o status: " . $e->getMessage());
}
?>