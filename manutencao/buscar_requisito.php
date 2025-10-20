<?php
include '../conexao.php';

// Verifica se foi fornecido o parâmetro 'maquina_ni' na URL
if (isset($_GET['maquina_ni'])) {
    $maquinaNi = $_GET['maquina_ni'];

    // Consulta o id da máquina com base no 'maquina_ni'
    $sql_maquina = "SELECT idmaquina FROM maquina WHERE maquina_ni = :maquina_ni LIMIT 1";
    $stmt_maquina = $pdo->prepare($sql_maquina);
    $stmt_maquina->bindValue(':maquina_ni', $maquinaNi, PDO::PARAM_STR);
    $stmt_maquina->execute();
    $maquinaId = $stmt_maquina->fetchColumn();

    if ($maquinaId) {
        // Consulta os requisitos_ids e requisitos_especifico_ids da tabela defeitos para o 'maquina_id'
        $sql_defeito = "SELECT requisitos_ids, requisitos_especifico_ids FROM defeitos WHERE maquina_id = :maquina_id LIMIT 1";
        $stmt_defeito = $pdo->prepare($sql_defeito);
        $stmt_defeito->bindValue(':maquina_id', $maquinaId, PDO::PARAM_INT);
        $stmt_defeito->execute();
        $defeito = $stmt_defeito->fetch(PDO::FETCH_ASSOC);

        if ($defeito) {
           
            // Verifica se existem requisitos gerais a serem puxados
            if (!empty($defeito['requisitos_ids'])) {
                // Separa os ids em um array
                $requisitos_ids = explode(",", $defeito['requisitos_ids']);
               

                // Filtra para pegar somente os requisitos válidos que existem na tabela 'requisitos'
                $sql_requisitos = "SELECT requisito_topico FROM requisitos WHERE idrequisitos IN (" . implode(",", array_map('intval', $requisitos_ids)) . ")";
                $stmt_requisitos = $pdo->query($sql_requisitos);
                $requisitos = $stmt_requisitos->fetchAll(PDO::FETCH_ASSOC);

                if ($requisitos) {
                    echo "<h3>Requisitos Gerais:</h3>";
                    echo "<ul>";
                    foreach ($requisitos as $requisito) {
                        echo "<li>" . htmlspecialchars($requisito['requisito_topico']) . "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>Nenhum requisito geral encontrado para esta máquina.</p>";
                }
            } else {
                echo "Nenhum requisito geral foi encontrado para o defeito. <br>";  // Debug
            }

            // Verifica se existem requisitos específicos a serem puxados
            if (!empty($defeito['requisitos_especifico_ids'])) {
                // Separa os ids em um array
                $requisitos_especificos_ids = explode(",", $defeito['requisitos_especifico_ids']);
                echo "IDs dos requisitos específicos: " . implode(",", $requisitos_especificos_ids) . "<br>";  // Debug

                // Filtra para pegar somente os requisitos específicos válidos que existem na tabela 'maquina_requisitos'
                $sql_requisitos_especificos = "SELECT requisitos_especificos FROM maquina_requisitos WHERE idmaquina_requisitos IN (" . implode(",", array_map('intval', $requisitos_especificos_ids)) . ")";
                $stmt_requisitos_especificos = $pdo->query($sql_requisitos_especificos);
                $requisitos_especificos = $stmt_requisitos_especificos->fetchAll(PDO::FETCH_ASSOC);

                if ($requisitos_especificos) {
                    echo "<h3>Requisitos Específicos:</h3>";
                    echo "<ul>";
                    foreach ($requisitos_especificos as $requisito) {
                        echo "<li>" . htmlspecialchars($requisito['requisitos_especificos']) . "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>Nenhum requisito específico encontrado para esta máquina.</p>";
                }
            } else {
                echo "Nenhum requisito específico foi encontrado para o defeito. <br>";  // Debug
            }
        } else {
            echo "Nenhum defeito encontrado para essa máquina. <br>";  // Debug
        }
    } else {
        echo "Máquina não encontrada. <br>";  // Debug
    }
}

// Habilitar exibição de erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
