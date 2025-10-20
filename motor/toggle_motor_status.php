<?php
include '../conexao.php';

try {
    $idMotor = $_GET['id'];

    // Consulta o status atual
    $sql = "SELECT motor_status FROM motor WHERE idmotor = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $idMotor]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $statusAtual = $row['motor_status'];

    // Inverte o status
    $novoStatus = ($statusAtual === 'Inativo') ? 'Ativo' : 'Inativo';

    // Atualiza o status no banco de dados
    $sql = "UPDATE motor SET motor_status = :novoStatus WHERE idmotor = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['novoStatus' => $novoStatus, 'id' => $idMotor]);

    header("Location: consulta.php"); // Redireciona para a página de lista

} catch (PDOException $e) {
    die("Erro ao atualizar o status: " . $e->getMessage());
}
?>
