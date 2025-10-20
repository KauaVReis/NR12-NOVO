<?php
include '../conexao.php'; // Verifique o caminho correto

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    date_default_timezone_set('America/Sao_Paulo'); // Configura o fuso horário

    $maquina_id = $_POST["maquina_id"];
    $aluno_id = $_POST["aluno_id"];
    $colaborador_id = $_POST["colaborador_id"];

    // Recebe os IDs dos requisitos gerais e específicos marcados
    $requisitos_id = $_POST["requisitos_ids"] ?? []; // Requisitos gerais
    $requisitos_especifico_id = $_POST["requisitos_especifico_ids"] ?? []; // Requisitos específicos

    // Verifica se todos os requisitos foram marcados
    if (!empty($requisitos_id) && !empty($requisitos_especifico_id)) {
        // Definir data e hora do histórico
        $historico_data = date("Y-m-d");
        $historico_hora = date("H:i:s");

        // Insere cada requisito como um novo registro na tabela histórico
        $sql = "INSERT INTO historico (maquina_id, aluno_id, colaborador_id, historico_data, historico_hora, historico_status, requisito_id, requisito_especifico_id)
                VALUES (:maquina_id, :aluno_id, :colaborador_id, :historico_data, :historico_hora, :historico_status, :requisito_id, :requisito_especifico_id)";

        $stmt = $pdo->prepare($sql);

        // Inserir cada requisito padrão como "Checado"
        foreach ($requisitos_id as $requisito_id) {
            $stmt->execute([
                ':maquina_id' => $maquina_id,
                ':aluno_id' => $aluno_id,
                ':colaborador_id' => $colaborador_id,
                ':historico_data' => $historico_data,
                ':historico_hora' => $historico_hora,
                ':historico_status' => 'Checado',
                ':requisito_id' => $requisito_id,
                ':requisito_especifico_id' => null,
            ]);
        }

        // Inserir cada requisito específico como "Checado"
        foreach ($requisitos_especifico_id as $requisito_especifico_id) {
            $stmt->execute([
                ':maquina_id' => $maquina_id,
                ':aluno_id' => $aluno_id,
                ':colaborador_id' => $colaborador_id,
                ':historico_data' => $historico_data,
                ':historico_hora' => $historico_hora,
                ':historico_status' => 'Checado',
                ':requisito_id' => null,
                ':requisito_especifico_id' => $requisito_especifico_id,
            ]);
        }

        echo "Histórico cadastrado com sucesso!";
    } else {
        echo "Erro: Todos os requisitos devem ser marcados para cadastrar no histórico.";
    }
}
?>
