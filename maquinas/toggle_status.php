
<?php
include '../conexao.php';

try {
    $idMaquina = $_GET['id'];

    // Consulta o status atual
    $sql = "SELECT maquina_status FROM maquina WHERE idmaquina = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $idMaquina]);
    $maquina = $stmt->fetch(PDO::FETCH_ASSOC);
    $statusAtual = $maquina['maquina_status'];

    // Inverte o status
    $novoStatus = ($statusAtual === 'Inativo') ? 'Ativo' : 'Inativo';

    // Atualiza o status no banco de dados
    $sql = "UPDATE maquina SET maquina_status = :novoStatus WHERE idmaquina = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['novoStatus' => $novoStatus, 'id' => $idMaquina]);

    header("Location: consulta.php"); // Redireciona para a página de lista

} catch (PDOException $e) {
    die("Erro ao atualizar o status: " . $e->getMessage());
}
?>