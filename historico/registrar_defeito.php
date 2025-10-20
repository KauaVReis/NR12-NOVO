<?php
// Configuração da conexão com o banco de dados
include '../conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Captura os dados do formulário
    $descricao = $_POST['descricao'];
    $colaborador_id = $_POST['colaborador_id_modal'];
    $aluno_id = $_POST['aluno_id'];
    $maquina_id = $_POST['maquina_id'];

    // Captura os requisitos não selecionados enviados no modal
    $requisitos_nao_checados = $_POST['requisitos_nao_checados'] ?? [];
    $requisitos_especifico_ids_nao_checados = $_POST['requisitos_nao_checados_especificos'] ?? [];

    try {
        // Inserir os requisitos gerais não checados que foram enviados no modal
        if (!empty($requisitos_nao_checados)) {
            foreach ($requisitos_nao_checados as $requisito_id) {
                $sqlDefeito = "INSERT INTO defeitos (descricao, colaborador_id, aluno_id, maquina_id, requisitos_ids, requisitos_especifico_ids) 
                               VALUES (:descricao, :colaborador_id, :aluno_id, :maquina_id, :requisitos_ids, NULL)";
                $stmtDefeito = $pdo->prepare($sqlDefeito);
                $stmtDefeito->bindParam(':descricao', $descricao);
                $stmtDefeito->bindParam(':colaborador_id', $colaborador_id);
                $stmtDefeito->bindParam(':aluno_id', $aluno_id);
                $stmtDefeito->bindParam(':maquina_id', $maquina_id);
                $stmtDefeito->bindParam(':requisitos_ids', $requisito_id);
                $stmtDefeito->execute();
            }
        }

        // Inserir os requisitos específicos não checados que foram enviados no modal
        if (!empty($requisitos_especifico_ids_nao_checados)) {
            foreach ($requisitos_especifico_ids_nao_checados as $requisito_especifico_id) {
                $sqlDefeito = "INSERT INTO defeitos (descricao, colaborador_id, aluno_id, maquina_id, requisitos_ids, requisitos_especifico_ids) 
                               VALUES (:descricao, :colaborador_id, :aluno_id, :maquina_id, NULL, :requisitos_especifico_ids)";
                $stmtDefeito = $pdo->prepare($sqlDefeito);
                $stmtDefeito->bindParam(':descricao', $descricao);
                $stmtDefeito->bindParam(':colaborador_id', $colaborador_id);
                $stmtDefeito->bindParam(':aluno_id', $aluno_id);
                $stmtDefeito->bindParam(':maquina_id', $maquina_id);
                $stmtDefeito->bindParam(':requisitos_especifico_ids', $requisito_especifico_id);
                $stmtDefeito->execute();
            }
        }

        // Atualizar o status da máquina para 'Inativo' apenas se houve registro de defeitos
        if (!empty($requisitos_nao_checados) || !empty($requisitos_especifico_ids_nao_checados)) {
            $sqlUpdateStatus = "UPDATE maquina SET maquina_status = 'Inativo' WHERE idmaquina = :maquina_id";
            $stmtUpdateStatus = $pdo->prepare($sqlUpdateStatus);
            $stmtUpdateStatus->bindParam(':maquina_id', $maquina_id);
            $stmtUpdateStatus->execute();
        }

        // Mensagem de sucesso
        header("location:../Login/login.php");
        $mensagem = "Defeito registrado com sucesso e status da máquina alterado para Inativo!";
    } catch (PDOException $e) {
        $mensagem = "Erro ao registrar defeito: " . $e->getMessage();
    }
}
?>

<!-- HTML para mostrar a mensagem de sucesso ou erro -->
<div id="toast" class="toast"><?= htmlspecialchars($mensagem) ?></div>
    