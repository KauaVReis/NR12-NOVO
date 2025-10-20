<?php
include '../conexao.php';

$id = $_GET['id'];
$dataAtual = date('Y-m-d H:i:s');

$query = "UPDATE manutencao 
          SET manutencao_realizada = :dataAtual, manutencao_estado = 'Consertado' 
          WHERE idmanutencao = :id";
$stmt = $pdo->prepare($query);

$stmt->bindParam(':dataAtual', $dataAtual);
$stmt->bindParam(':id', $id);

if ($stmt->execute()) {
  echo "Manutenção realizada atualizada com sucesso!";
} else {
  echo "Erro ao atualizar a manutenção realizada.";
}
?>
<meta http-equiv="refresh" content="0; URL='tabela.php'">