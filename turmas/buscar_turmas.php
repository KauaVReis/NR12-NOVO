<?php
include '../conexao.php';

// Função para buscar turmas com filtros combinados
function buscarTurmas($pdo, $termoPesquisa, $periodo, $offset, $resultadosPorPagina, $statusFilter = '')
{
    $sql = "
        SELECT
            t.idturmas,
            t.turma_nome,
            t.turma_periodo,
            t.turma_inicio,
            t.turma_fim,
            c.curso_nome,
            col.colaborador_nome,
            t.turmas_status
        FROM turmas t
        LEFT JOIN curso c ON t.curso_id = c.idcurso
        LEFT JOIN colaborador col ON t.colaborador_id = col.idcolaborador
        WHERE (t.turma_nome LIKE :termoPesquisa OR c.curso_nome LIKE :termoPesquisa)
    ";

    // Adiciona filtro por período, se fornecido
    if (!empty($periodo)) {
        $sql .= " AND t.turma_periodo = :periodo";
    }

    // Adiciona filtro por status, se fornecido
    if (!empty($statusFilter)) {
        $sql .= " AND t.turmas_status = :statusFilter";
    }

    $sql .= " ORDER BY t.idturmas ASC LIMIT :offset, :resultadosPorPagina";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':termoPesquisa', '%' . $termoPesquisa . '%', PDO::PARAM_STR);

    if (!empty($periodo)) {
        $stmt->bindValue(':periodo', $periodo, PDO::PARAM_STR);
    }

    if (!empty($statusFilter)) {
        $stmt->bindValue(':statusFilter', $statusFilter, PDO::PARAM_STR);
    }

    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':resultadosPorPagina', $resultadosPorPagina, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para contar turmas com filtros combinados
function contarTurmas($pdo, $termoPesquisa, $periodo, $statusFilter = '')
{
    $sql = "
        SELECT COUNT(*) AS total
        FROM turmas t
        LEFT JOIN curso c ON t.curso_id = c.idcurso
        WHERE (t.turma_nome LIKE :termoPesquisa OR c.curso_nome LIKE :termoPesquisa)
    ";

    if (!empty($periodo)) {
        $sql .= " AND t.turma_periodo = :periodo";
    }

    if (!empty($statusFilter)) {
        $sql .= " AND t.turmas_status = :statusFilter";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':termoPesquisa', '%' . $termoPesquisa . '%', PDO::PARAM_STR);

    if (!empty($periodo)) {
        $stmt->bindValue(':periodo', $periodo, PDO::PARAM_STR);
    }

    if (!empty($statusFilter)) {
        $stmt->bindValue(':statusFilter', $statusFilter, PDO::PARAM_STR);
    }

    $stmt->execute();
    return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

?>
