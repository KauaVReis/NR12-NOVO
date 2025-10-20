<?php
include '../conexao.php';

if (isset($_GET['tipomaquina_id'])) {
    $tipomaquina_id = $_GET['tipomaquina_id'];

    $stmt = $pdo->prepare("SELECT tiporequisito_id, tipo_req FROM requisitos WHERE tipomaquina_id = :tipomaquina_id");
    $stmt->bindParam(':tipomaquina_id', $tipomaquina_id);
    $stmt->execute();

    $requisitos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($requisitos);
}
?>
