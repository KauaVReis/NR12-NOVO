<?php
include '../conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $maquina_id = $_POST['maquina_id'];
    $colaborador_id = $_POST['colaborador_id'];
    $tipo_manutencao = isset($_POST['tipo_manutencao']) ? 'Preventiva' : 'Corretiva';
    $manutencao_descricao = $tipo_manutencao === 'Corretiva' ? $_POST['manutencao_descricao'] : null;
    $manutencao_data = $tipo_manutencao === 'Preventiva' ? $_POST['manutencao_data'] : date('Y-m-d H:i:s');
    $manutencao_estado = $_POST['manutencao_estado'];
    $manutencao_status = $_POST['manutencao_status'];
    $manutencao_realizada = $_POST['manutencao_realizada'] ? $_POST['manutencao_realizada'] : null;

    // Atualiza os dados da manutenção
    $updateQuery = "UPDATE manutencao 
                    SET maquina_id = :maquina_id, colaborador_id = :colaborador_id, 
                        manutencao_data = :manutencao_data, manutencao_descricao = :manutencao_descricao,
                        tipo_manutencao = :tipo_manutencao, manutencao_estado = :manutencao_estado, 
                        manutencao_status = :manutencao_status, manutencao_realizada = :manutencao_realizada 
                    WHERE idmanutencao = :id";
    $stmt = $pdo->prepare($updateQuery);

    // Liga os parâmetros com os valores do formulário
    $stmt->bindParam(':maquina_id', $maquina_id, PDO::PARAM_INT);
    $stmt->bindParam(':colaborador_id', $colaborador_id, PDO::PARAM_INT);
    $stmt->bindParam(':manutencao_data', $manutencao_data);
    $stmt->bindParam(':manutencao_descricao', $manutencao_descricao);
    $stmt->bindParam(':tipo_manutencao', $tipo_manutencao);
    $stmt->bindParam(':manutencao_estado', $manutencao_estado);
    $stmt->bindParam(':manutencao_status', $manutencao_status);
    $stmt->bindParam(':manutencao_realizada', $manutencao_realizada);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Executa a atualização e verifica o resultado
    if ($stmt->execute()) {
        echo "Manutenção atualizada com sucesso!";
    } else {
        echo "Erro ao atualizar a manutenção.";
    }
}
?>
<meta http-equiv="refresh" content="0; URL='tabela.php'">