<?php
include '../conexao.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Consultar o status atual
    $sql = "SELECT tipomaquina_status FROM tipomaquina WHERE idtipomaquina = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $status = $stmt->fetchColumn();

    // Alternar o status
    $novo_status = $status === 'Ativo' ? 'Inativo' : 'Ativo';

    // Atualizar o status no banco de dados
    $sql_update = "UPDATE tipomaquina SET tipomaquina_status = :status WHERE idtipomaquina = :id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute(['status' => $novo_status, 'id' => $id]);

    // Retornar o novo status como resposta JSON
    echo json_encode(['success' => true, 'new_status' => $novo_status]);
}
?>
