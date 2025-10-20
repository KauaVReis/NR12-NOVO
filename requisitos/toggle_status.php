
<?php
include '../conexao.php';

try {
    $idRequisito = $_GET['id'];

    // Consulta o status atual
    $sql = "SELECT requisitos_status FROM requisitos WHERE idrequisitos = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $idRequisito]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $statusAtual = $row['requisitos_status'];

    // Inverte o status
    $novoStatus = ($statusAtual === 'Inativo') ? 'Ativo' : 'Inativo';

    // Atualiza o status no banco de dados
    $sql = "UPDATE requisitos SET requisitos_status = :novoStatus WHERE idrequisitos = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['novoStatus' => $novoStatus, 'id' => $idRequisito]);

    header("Location: consultar.php"); // Redireciona para a página de lista

} catch (PDOException $e) {
    die("Erro ao atualizar o status: " . $e->getMessage());
}
?>