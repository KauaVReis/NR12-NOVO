<?php
include '../conexao.php';

$termoPesquisa = isset($_GET['search']) ? $_GET['search'] : '';
$resultadosPorPagina = 5;
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaAtual - 1) * $resultadosPorPagina;

try {
    // Contagem total de cursos para a pesquisa
    $sqlCount = "
        SELECT COUNT(*) 
        FROM curso 
        WHERE curso_nome LIKE :termoPesquisa
    ";
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->bindValue(':termoPesquisa', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
    $stmtCount->execute(); 
    $totalRegistros = $stmtCount->fetchColumn();
    $totalPaginas = ceil($totalRegistros / $resultadosPorPagina);

    // Seleção dos cursos
    $sql = "
        SELECT idcurso, curso_nome, curso_status 
        FROM curso 
        WHERE curso_nome LIKE :termoPesquisa
        LIMIT :offset, :resultadosPorPagina
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':termoPesquisa', '%' . $termoPesquisa . '%', PDO::PARAM_STR);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':resultadosPorPagina', $resultadosPorPagina, PDO::PARAM_INT);
    $stmt->execute();

    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retorna os cursos e o total de páginas
    echo json_encode(['cursos' => $cursos, 'totalPaginas' => $totalPaginas]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro na consulta: ' . $e->getMessage()]);
}
?>
