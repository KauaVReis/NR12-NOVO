<?php
// Incluindo a conexão com o banco de dados
include '../conexao.php';

try {
    // Definindo o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Atualizando o status das máquinas cuja data de próxima manutenção já passou
    $sqlUpdate = "
        UPDATE maquina
        SET maquina_status = 'Inativo'
        WHERE data_proxima_manutencao < CURDATE() AND maquina_status = 'Ativo'
    ";
    $pdo->exec($sqlUpdate);

     //echo "Status das máquinas atualizado com sucesso.";
} catch (PDOException $e) {
    die("Falha ao atualizar status das máquinas: " . $e->getMessage());
}
?>
