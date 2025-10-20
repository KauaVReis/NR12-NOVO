<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
define('BASE_URL', '../../nr12/');

include $_SERVER['DOCUMENT_ROOT'] . '/nr12/sidebar.php';
include('../conexao.php');

// Parâmetros de busca e paginação
$search = isset($_GET['search']) ? $_GET['search'] : '';
$paginaAtual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$resultadosPorPagina = 10;
$offset = ($paginaAtual - 1) * $resultadosPorPagina;

$searchSql = "";
if ($search) {
    $searchSql = " AND (c.colaborador_nome LIKE :search OR se.desc_erro LIKE :search)";
}

// Consulta total de registros para paginação
$totalSql = "SELECT COUNT(*) FROM solicitacao_erro se
             JOIN colaborador c ON se.id_colaborador = c.idcolaborador
             WHERE 1=1 $searchSql";
$totalStmt = $pdo->prepare($totalSql);
if ($search) {
    $totalStmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
$totalStmt->execute();
$totalResultados = $totalStmt->fetchColumn();
$totalPaginas = ceil($totalResultados / $resultadosPorPagina);

// Consulta com filtro de pesquisa e limite para paginação
$sql = "SELECT se.idsolicitacao_erro, se.id_colaborador, se.desc_erro, se.situacao, 
               se.data_solicitacao, se.data_solucao, c.colaborador_nome 
        FROM solicitacao_erro se
        JOIN colaborador c ON se.id_colaborador = c.idcolaborador
        WHERE 1=1 $searchSql
        ORDER BY se.data_solicitacao DESC
        LIMIT :offset, :resultadosPorPagina";
$stmt = $pdo->prepare($sql);
if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':resultadosPorPagina', $resultadosPorPagina, PDO::PARAM_INT);
$stmt->execute();

// Se for uma requisição AJAX, retorna apenas a tabela
if (isset($_GET['ajax'])) {
    if ($stmt->rowCount() > 0) {
        echo "<table class='TabelaErro'>
        <thead>
            <tr>
                <th>Nome do Colaborador</th>
                <th>Descrição do Erro</th>
                <th>Situação</th>
                <th>Data da Solicitação</th>
                <th>Data da Solução</th>";
        if ($_SESSION['colaborador_permissao'] == 'Adm') {
            echo "<th>Ações</th>";
        }
        echo "</tr>
        </thead>
        <tbody>";

        foreach ($stmt as $row) {
            $data_solicitacao = date('d/m/Y', strtotime($row['data_solicitacao']));
            $data_solucao = !empty($row['data_solucao']) ? date('d/m/Y', strtotime($row['data_solucao'])) : 'Não Resolvido';

            echo "<tr>
                <td>" . htmlspecialchars($row['colaborador_nome']) . "</td>
                <td>" . htmlspecialchars($row['desc_erro']) . "</td>
                <td>" . htmlspecialchars($row['situacao']) . "</td>
                <td>" . $data_solicitacao . "</td>
                <td>" . $data_solucao . "</td>";

            if ($_SESSION['colaborador_permissao'] == 'Adm' && $row['situacao'] != 'Resolvido') {
                echo "<td>
                    <form class='btnresolvido' method='post' action='resolver.php'>
                        <input type='submit' value='Resolvido'>
                        <input type='hidden' name='idsolicitacao_erro' value='" . htmlspecialchars($row['idsolicitacao_erro']) . "'>
                    </form>
                </td>";
            }

            echo "</tr>";
        }

        echo "</tbody>
        </table>";
    } else {
        echo "<div style='color: red;'>Nenhum resultado encontrado.</div>";
    }

    // Adiciona os controles de paginação
    echo "<div id='paginationControls'>";
     // Quantidade máxima de botões a serem exibidos
     $maxLinks = 3;

     // Determina os limites de página
     $start = max(1, $paginaAtual - floor($maxLinks / 2));
     $end = min($totalPaginas, $start + $maxLinks - 1);
 
     // Ajusta o início caso o fim alcance o total de páginas
     $start = max(1, $end - $maxLinks + 1);
 
     // Botão "Anterior"
     if ($paginaAtual > 1) {
         echo "<a href='#' class='pagination-button' onclick='changePage(" . ($paginaAtual - 1) . ")'>&laquo; Anterior</a>";
     }
 
     // Botões de páginas no intervalo definido
     for ($i = $start; $i <= $end; $i++) {
         $activeClass = $i == $paginaAtual ? 'current-page' : '';
         echo "<a href='#' class='pagination-button $activeClass' onclick='changePage($i)'>$i</a>";
     }
 
     // Botão "Próximo"
     if ($paginaAtual < $totalPaginas) {
         echo "<a href='#' class='pagination-button' onclick='changePage(" . ($paginaAtual + 1) . ")'>Próximo &raquo;</a>";
     }
    echo "</div>";




    exit;

}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Busca Dinâmica</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body {
            display: flex;
            justify-content: center;
        }

        .botaoresolvido {
            background-color: #e21616;
            color: #ffffff;
            padding: 6px 14px;
            margin: 12px 0;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
            transition: background-color 0.3s, box-shadow 0.3s, transform 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);

        }

        h2 {
            text-align: center;
        }

        /* Estilos de tabela e paginação */
        .container_erro {
            margin: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .tabelaconsulta {
            margin: auto;
        }

        .TabelaErro {

            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            margin-top: 20px;
        }

        .TabelaErro th,
        .TabelaErro td {
            padding: 8px;
            text-align: center;
        }

        .TabelaErro th {
            background-color: #a10000;
            color: white;
            font-weight: bold;
            border: none;
        }

        #paginationControls {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        .pagination-button {
            margin: 0 5px;
            padding: 5px 10px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            color: black;
            background-color: #fff;
        }

        .pagination-button:hover {
            font-weight: bold;
            background-color: gainsboro;
        }

        .current-page {
            background-color: gainsboro;
            color: black;
            font-weight: bold;
            border-radius: 4px;
        }

        #searchForm {
            text-align: center;
            margin: 20px auto;
            padding: 10px;
            width: 80%;
            max-width: 500px;
            background-color: transparent;
            border-radius: 8px;
            box-shadow: 0px 2px 6px rgba(0, 0, 0, 0);
            border: none;
        }

        #searchForm input[type="text"] {
            padding: 8px;
            width: 70%;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }

        #paginationControls {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        .pagination-button {
            margin: 0 5px;
            padding: 5px 10px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            color: black;
            background-color: #fff;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .pagination-button:hover {
            font-weight: bold;
            background-color: gainsboro;
        }

        .current-page {
            background-color: gainsboro;
            color: black;
            font-weight: bold;
            border-radius: 4px;
        }

        .btnresolvido {
            margin: 0;
            width: 100%;
            border: none;
            box-shadow: none;

            background-color: transparent;
        }

        .btnresolvido:hover {
            margin: 0;
            width: 100%;
            border: none;
            box-shadow: none;
            background-color: transparent;
        }
        @media (max-width: 480px) {
        .container {
            margin-top: 18%;
        }

        .table-container {
            padding: 0 25px;
            width: 100%;
            overflow-y: auto;

            /* Adiciona rolagem vertical */
        }

        table,
        thead,
        tbody,
        th,
        td,
        tr {
            display: block;
            width: 100%;
            padding: 5px 8px;
        }

        tbody {
            background-color: #e0e0e0;
            border-radius: 5px;
        }

        th,
        td {
            font-size: 0.8em;
            padding: 5px;
        }

        tr {
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
            margin-bottom: 8px;
            background-color: #f8f8f8;
            border-radius: 6px;
            box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.1);
        }

        td {
            display: flex;
            justify-content: space-between;
            padding: 5px 8px;
        }

        .table-container td::before {
            content: attr(data-label);
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            margin-right: 5px;
        }

        thead {
            display: none;
        }

        /* Alinha o filtro de aluno e de turma na mesma linha */
        .search-bar {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Alinha o label e o select do filtro de turma na mesma linha */
        .action-buttons {
            display: flex;
            flex-direction: row;
            gap: 1px;
        }

        .action-buttons a {
            width: 71px;
        }

        .reset-password-btn a {
            width: 120px;
        }

        .botoes {
            flex-direction: row !important;
        }
    }

    /* Estilo para o botão de fechar no modal */
    button {
        padding: 8px 16px;
        background-color: #B22222;
        color: white;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s;
        border-radius: 4px;
        margin-top: 10px;
    }

    button:hover {
        background-color: #c1000a;
    }

    /* Responsividade para Tablets */
    /* Ajustes para Tablet em Modo Retrato (Vertical) */
    @media (max-width: 906px) {
        .botoes {
            flex-direction: column;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            /* Reduz tamanho da fonte */
        }

        th,
        td {
            font-size: 12px;
            /* Fonte menor para caber melhor */
            padding: 6px;
            /* Menor espaçamento nas células */
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #b22222;
            color: #ffffff;
        }

        /* Ajuste para barra de busca e filtros */
        .search-input {
            width: 90%;
            max-width: 300px;
            /* Limite de largura para entrada de busca e filtros */
            font-size: 0.9em;
            margin-bottom: 10px;
        }
    }

    </style>
</head>

<body>
    <h2>Consulta Suporte</h2>
    <!-- Campo de busca -->
    <form id="searchForm" method="get" onsubmit="event.preventDefault();">
        <input type="text" id="searchInput" name="search" class="search-input consultafuncionario"
            placeholder="Pesquisar por nome ou descrição" value="<?= htmlspecialchars($search) ?>"
            oninput="updateSearch()">
    </form>

    <div id="resultsContainer">
        <!-- Resultados da busca carregados na primeira vez (sem AJAX) -->
        <?php if ($stmt->rowCount() > 0): ?>
            <table class="TabelaErro">
                <thead>
                    <tr>
                        <th>Nome do Colaborador</th>
                        <th>Descrição do Erro</th>
                        <th>Situação</th>
                        <th>Data da Solicitação</th>
                        <th>Data da Solução</th>
                        <?php
                        if ($_SESSION['colaborador_permissao'] == 'Adm') {
                            echo "
                                <th>Ações</th>
                                ";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stmt as $row): ?>
                        <?php
                        $data_solicitacao = date('d/m/Y', strtotime($row['data_solicitacao']));
                        $data_solucao = !empty($row['data_solucao']) ? date('d/m/Y', strtotime($row['data_solucao'])) : 'Não Resolvido';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['colaborador_nome']) ?></td>
                            <td><?= htmlspecialchars($row['desc_erro']) ?></td>
                            <td><?= htmlspecialchars($row['situacao']) ?></td>
                            <td><?= $data_solicitacao ?></td>
                            <td><?= $data_solucao ?></td>
                            <?php
                            if ($_SESSION['colaborador_permissao'] == 'Adm' && $row['situacao'] != 'Resolvido') {
                                echo "
                                <td>
                                    <form class='btnresolvido' method='post' action='resolver.php'>
                                        <input type='submit' value='Resolvido'>
                                        <input hidden type='text' name='idsolicitacao_erro' value='" . $row['idsolicitacao_erro'] . "'>
                                        </form>
                                        
                                </td>
                                ";
                            }
                            ?>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="color: red;">Nenhum resultado encontrado.</div>
        <?php endif; ?>

        <!-- Controles de paginação (apenas para a primeira exibição) -->
        <div id="paginationControls">
            <?php
            // Quantidade máxima de botões a serem exibidos
            $maxLinks = 3;

            // Determina os limites de página
            $start = max(1, $paginaAtual - floor($maxLinks / 2));
            $end = min($totalPaginas, $start + $maxLinks - 1);

            // Ajusta o início caso o fim alcance o total de páginas
            $start = max(1, $end - $maxLinks + 1);

            // Botão "Anterior"
            if ($paginaAtual > 1) {
                echo "<a href='#' class='pagination-button' onclick='changePage(" . ($paginaAtual - 1) . ")'>&laquo; Anterior</a>";
            }

            // Botões de páginas no intervalo definido
            for ($i = $start; $i <= $end; $i++) {
                $activeClass = $i == $paginaAtual ? 'current-page' : '';
                echo "<a href='#' class='pagination-button $activeClass' onclick='changePage($i)'>$i</a>";
            }

            // Botão "Próximo"
            if ($paginaAtual < $totalPaginas) {
                echo "<a href='#' class='pagination-button' onclick='changePage(" . ($paginaAtual + 1) . ")'>Próximo &raquo;</a>";
            }
            ?>
        </div>

    </div>

    <script>
        async function updateSearch() {
            const search = document.getElementById('searchInput').value;
            const currentPage = 1;
            await fetchResults(search, currentPage);
        }

        async function changePage(pageNumber) {
            const search = document.getElementById('searchInput').value;
            await fetchResults(search, pageNumber);
        }

        async function fetchResults(search, page) {
            try {
                const response = await fetch(`<?php echo $_SERVER['PHP_SELF']; ?>?search=${encodeURIComponent(search)}&pagina=${page}&ajax=1`);
                const html = await response.text();
                document.getElementById('resultsContainer').innerHTML = html;
            } catch (error) {
                console.error('Erro na busca:', error);
            }
        }
    </script>
</body>

</html>