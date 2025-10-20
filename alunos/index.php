<?php
include '../conexao.php';

$itens_por_pagina = 10;

$termo_busca = isset($_GET['query']) ? $_GET['query'] : '';
$turma_filtro = isset($_GET['turma']) ? $_GET['turma'] : '';
$status_filtro = isset($_GET['status']) ? $_GET['status'] : '';

try {
    // Buscar os alunos com base na busca, filtro de turma e status
    $sql = "SELECT a.idaluno, a.aluno_nome, a.aluno_matricula, t.turma_nome, a.aluno_status
            FROM aluno a
            JOIN turmas t ON a.turmas_id = t.idturmas
            WHERE (a.aluno_nome LIKE :busca OR a.aluno_matricula LIKE :busca 
                   OR t.turma_nome LIKE :busca OR a.aluno_status LIKE :busca)
            AND (t.idturmas LIKE :turma OR :turma = '')
            AND (aluno_status LIKE :status OR :status = '')
            LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':busca', "%$termo_busca%");
    $stmt->bindValue(':turma', "%$turma_filtro%");
    $stmt->bindValue(':status', $status_filtro);
    $stmt->bindValue(':limit', $itens_por_pagina, PDO::PARAM_INT);
    $stmt->execute();

    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($alunos)) {
        echo "<tr><td colspan='5' class='no-data'>Nenhum Aluno encontrado</td></tr>";
    } else {
        foreach ($alunos as $aluno) {
            echo "<tr>";
            echo "<td data-label='Nome'>" . htmlspecialchars($aluno['aluno_nome']) . "</td>";
            echo "<td data-label='Matrícula'>" . htmlspecialchars($aluno['aluno_matricula']) . "</td>";
            echo "<td data-label='Turma'>" . htmlspecialchars($aluno['turma_nome']) . "</td>";
            echo "<td data-label='Status'>" . htmlspecialchars($aluno['aluno_status']) . "</td>";
            echo "<td data-label='Ações' class='action-buttons'>
                   <a href='toggle_status.php?id=" . $aluno['idaluno'] . "' class='btn-inativar'>
                       " . ($aluno['aluno_status'] === 'Ativo' ? '<i class="fa fa-times"></i> Desativar' : '<i class="fa fa-check"></i> Ativar') . "
                   </a>
                   <a href='alterar.php?id=" . $aluno['idaluno'] . "' class='btn-editar'>
                       <i class='fas fa-pencil-alt'></i> Editar
                   </a>
                   <a href='excluir.php?id=" . $aluno['idaluno'] . "' class='btn-excluir'>
                       <i class='fas fa-trash-alt'></i> Excluir
                   </a>
               </td>";
            echo "</tr>";
        }
    }
} catch (PDOException $e) {
    echo "Erro ao buscar alunos: " . $e->getMessage();
}
?>
