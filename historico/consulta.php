<?php
// Configurações do PHP e variáveis de banco de dados.
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
define('BASE_URL', '../../nr12/');

include $_SERVER['DOCUMENT_ROOT'] . '/nr12/sidebar.php';
include "../conexao.php";

require_once '../verifica_permissao.php';
verificaPermissao(['Adm']);

$registros_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

$filtro_unico = isset($_GET['filtro']) ? trim($_GET['filtro']) : '';
$filtro_data_inicial = isset($_GET['filtro_data_inicial']) ? trim($_GET['filtro_data_inicial']) : '';
$filtro_data_final = isset($_GET['filtro_data_final']) ? trim($_GET['filtro_data_final']) : '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta SQL com filtro único e filtro por data
    $sql = "SELECT 
        h.historicoid,
        h.historico_data,
        h.historico_hora,
        h.historico_status,
        m.maquina_ni AS maquina_ni,
        a.aluno_nome AS aluno_nome,
        c.colaborador_nome AS colaborador_nome,
        r.requisito_topico AS requisito_topico,
        r.tipo_req AS tipo_req,
        mr.requisitos_especificos AS requisitos_especificos,
        tm.tipomaquina_nome AS tipomaquina_nome
    FROM historico h
    LEFT JOIN maquina m ON h.maquina_id = m.idmaquina
    LEFT JOIN aluno a ON h.aluno_id = a.idaluno
    LEFT JOIN colaborador c ON h.colaborador_id = c.idcolaborador
    LEFT JOIN requisitos r ON h.requisito_id = r.idrequisitos
    LEFT JOIN maquina_requisitos mr ON h.maquina_id = mr.maquina_id
    LEFT JOIN tipomaquina tm ON m.tipomaquina_id = tm.idtipomaquina
    WHERE 1=1";

    if (!empty($filtro_unico)) {
        $sql .= " AND (
            a.aluno_nome LIKE :filtro OR 
            m.maquina_ni LIKE :filtro OR 
            h.historico_data LIKE :filtro OR
            c.colaborador_nome LIKE :filtro OR
            tm.tipomaquina_nome LIKE :filtro
        )";
    }

    if (!empty($filtro_data_inicial)) {
        $sql .= " AND h.historico_data >= :filtro_data_inicial";
    }

    if (!empty($filtro_data_final)) {
        $sql .= " AND h.historico_data <= :filtro_data_final";
    }

    $sql .= " ORDER BY h.historico_data DESC, h.historico_hora DESC LIMIT :offset, :registros";

    $stmt = $conn->prepare($sql);

    if (!empty($filtro_unico)) {
        $stmt->bindValue(':filtro', '%' . $filtro_unico . '%', PDO::PARAM_STR);
    }

    if (!empty($filtro_data_inicial)) {
        $stmt->bindValue(':filtro_data_inicial', $filtro_data_inicial, PDO::PARAM_STR);
    }

    if (!empty($filtro_data_final)) {
        $stmt->bindValue(':filtro_data_final', $filtro_data_final, PDO::PARAM_STR);
    }

    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':registros', $registros_por_pagina, PDO::PARAM_INT);
    $stmt->execute();

    $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obter o número total de registros para paginação
    $sql_total = "SELECT COUNT(*) FROM historico h
        LEFT JOIN maquina m ON h.maquina_id = m.idmaquina
        LEFT JOIN aluno a ON h.aluno_id = a.idaluno
        LEFT JOIN colaborador c ON h.colaborador_id = c.idcolaborador
        LEFT JOIN tipomaquina tm ON m.tipomaquina_id = tm.idtipomaquina
        WHERE 1=1";

    if (!empty($filtro_unico)) {
        $sql_total .= " AND (
            a.aluno_nome LIKE :filtro_total OR 
            m.maquina_ni LIKE :filtro_total OR 
            h.historico_data LIKE :filtro_total OR
            c.colaborador_nome LIKE :filtro_total OR
            tm.tipomaquina_nome LIKE :filtro_total
        )";
    }

    if (!empty($filtro_data_inicial)) {
        $sql_total .= " AND h.historico_data >= :filtro_data_inicial";
    }

    if (!empty($filtro_data_final)) {
        $sql_total .= " AND h.historico_data <= :filtro_data_final";
    }

    $stmt_total = $conn->prepare($sql_total);

    if (!empty($filtro_unico)) {
        $stmt_total->bindValue(':filtro_total', '%' . $filtro_unico . '%', PDO::PARAM_STR);
    }
    if (!empty($filtro_data_inicial)) {
        $stmt_total->bindValue(':filtro_data_inicial', $filtro_data_inicial, PDO::PARAM_STR);
    }

    if (!empty($filtro_data_final)) {
        $stmt_total->bindValue(':filtro_data_final', $filtro_data_final, PDO::PARAM_STR);
    }

    $stmt_total->execute();
    $total_registros = $stmt_total->fetchColumn();
    $total_paginas = ceil($total_registros / $registros_por_pagina);
} catch (PDOException $e) {
    echo "Erro ao buscar histórico: " . $e->getMessage();
    $historico = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Histórico</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            const searchDataInicialInput = document.getElementById('searchDataInicialInput');
            const searchDataFinalInput = document.getElementById('searchDataFinalInput');

            // Adiciona evento de 'keyup' para atualização dos filtros antes de enviar
            searchInput.addEventListener('keyup', function () {
                document.getElementById('hiddenFiltro').value = searchInput.value;
            });

            // Adiciona evento de 'input' para atualização do filtro de data inicial
            searchDataInicialInput.addEventListener('input', function () {
                document.getElementById('hiddenFiltroDataInicial').value = searchDataInicialInput.value;
            });

            // Adiciona evento de 'input' para atualização do filtro de data final
            searchDataFinalInput.addEventListener('input', function () {
                document.getElementById('hiddenFiltroDataFinal').value = searchDataFinalInput.value;
            });

        });

         document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const filtro = urlParams.get('filtro');
            const filtroDataInicial = urlParams.get('filtro_data_inicial');
             const filtroDataFinal = urlParams.get('filtro_data_final');
            if (filtro) {
                document.getElementById('searchInput').value = filtro;
            }
            if (filtroDataInicial) {
                document.getElementById('searchDataInicialInput').value = filtroDataInicial;
            }
             if (filtroDataFinal) {
                document.getElementById('searchDataFinalInput').value = filtroDataFinal;
            }
        });



        let debounceTimeout;

        function filtrarHistorico() {
            const input = document.getElementById('searchInput').value;
            const dataInicial = document.getElementById('searchDataInicialInput').value;
            const dataFinal = document.getElementById('searchDataFinalInput').value;


            clearTimeout(debounceTimeout);

            debounceTimeout = setTimeout(() => {
                const url = new URL(window.location.href);
                url.searchParams.set('filtro', input);
                url.searchParams.set('filtro_data_inicial', dataInicial);
                url.searchParams.set('filtro_data_final', dataFinal);
                url.searchParams.delete('pagina');
                history.replaceState(null, '', url);

                atualizarTabela(url);
            }, 300);
        }

        function atualizarTabela(url) {
            fetch(url)
                .then(response => response.text())
                .then(data => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(data, 'text/html');
                    const novaTabela = doc.querySelector('#tableHistorico tbody');
                    const novaPaginacao = doc.querySelector('.pagination');

                    document.querySelector('#tableHistorico tbody').innerHTML = novaTabela.innerHTML;
                    document.querySelector('.pagination').innerHTML = novaPaginacao.innerHTML;
                })
                .catch(error => console.error('Erro ao atualizar tabela:', error));
        }

        function imprimirTabela() {
            const logo = document.querySelector('.logo-senai');
            const actions = document.querySelector('.actions');
            const pagination = document.querySelector('.pagination');

            // Exibe a logo
            logo.style.display = 'block';

            // Oculta elementos desnecessários na impressão
            actions.style.display = 'none';
            pagination.style.display = 'none';

            // Aguarda a renderização completa antes de iniciar a impressão
            setTimeout(() => {
                window.print();

                // Restaura a visibilidade após a impressão
                actions.style.display = 'block';
                pagination.style.display = 'block';
                logo.style.display = 'none';
            }, 1);
        }

    </script>
</head>

<style>
    body {
        height: auto;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .btn-excel {
        background-color: red;
        /* Um tom de vermelho escuro */
        border: 1px solid black;
        color: white;
        font-weight: bold;
        padding: 8px 12px;
        border-radius: 4px;
        margin-top: -65px;
        margin-left: 980px;
        text-decoration: none;
        transition: background-color 0.3s;
        display: block;
    }

    .btn-imprimir {
        background-color: red;
        /* Um tom de vermelho escuro */
        border: 1px solid black;
        color: white;
        font-weight: bold;
        padding: 8px 12px;
        border-radius: 4px;
        margin-top: -65px;
        text-decoration: none;
        transition: background-color 0.3s;
        display: block;
    }

    .btn-excluir:hover {
        background-color: #A52A2A;
        /* Um tom mais claro ao passar o mouse */
    }

    body {
        display: flex;
        justify-content: center;
        text-align: center;
    }

    .pagination {
        margin: 20px auto;
        text-align: center;
        padding-bottom: 40px;
        width: 100%;
        max-width: 100%;
    }

    .pagination a {
        text-decoration: none;
        padding: 4px 7px;
        /* Aumenta o espaçamento para facilitar o clique */
        color: black;
        margin: 0 5px;
        transition: background-color 0.3s;
        background-color: #fff;
        border-radius: 1px;
    }

    .pagination a:hover {
        background-color: gray;
        font-weight: bold;
    }

    .current-page {
        background-color: dimgray;
        color: white;
    }

    .search-input {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 250px;
        max-width: 100%;
        cursor: pointer;
    }

    .actions {
        margin: 10px 0;
        text-align: center;
    }

    .actions form {
        background-color: transparent;
        border: none;
        box-shadow: none;
    }

    .actions button {
        padding: 8px 12px;
        margin: 5px;
        background-color: red;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .actions button:hover {
        background-color: darkred;
    }

    .logo-senai {
        display: none;
        /* A logo fica oculta por padrão */
        margin: 20px auto;
        text-align: center;
        width: 200px;
    }

    @media print {
        body {
            visibility: hidden;
            /* Esconde tudo fora da tabela */
        }

        #tableHistorico {
            visibility: visible;
            margin: 0 auto;
            /* Centraliza a tabela */
            width: 100%;
            /* Ajusta a largura */
        }

        #tableHistorico th,
        #tableHistorico td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .pagination,
        .actions {
            display: none;
            /* Oculta os botões e paginação */
        }

        h2.title_logs {
            visibility: visible;
            text-align: center;
        }

        .logo-senai {
            display: block;
            visibility: visible;
            /* Exibe a logo na impressão */
        }
    }

    /* Estilo adicional para tornar a tabela responsiva em dispositivos móveis */
    .table_logs {
        width: 100%;
        border-collapse: collapse;
    }

    /* Container com rolagem horizontal para tabelas grandes */
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        /* Melhora o comportamento de rolagem no iPhone */
        margin: 20px 0;
    }

    .sidebar_sidebar {
        z-index: 999;
        /* Maior que o z-index da tabela para garantir que fique acima */
    }

    .form-excel {
        margin: 0;
    }

    /* Ajuste da tabela para telas menores */
    /* Responsividade para dispositivos móveis */
    @media (max-width: 790px) {
        
        .pagination {
            margin: 10px;
            padding-bottom: 20px;
        }

        .pagination a {
            font-size: 12px;
            padding: 6px 10px;
            margin: 0 2px;
        }

        .table-container {
            margin-top: 70px;
            padding: 0 20px;
            border-radius: 8px;
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


        thead {
            display: none;
        }

        tbody {
            background-color: #e0e0e0;
            border-radius: 5px;
        }

        tr {
            border: 1px solid #ddd;
            margin-bottom: 10px;
            border-radius: 8px;
            background-color: #f8f8f8;
            box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.1);
            padding: 8px;
        }

        td {
            display: flex;
            justify-content: space-between;
            padding: 5px 8px;
            font-size: 0.7em;
        }

        .table-container td::before {
            content: attr(data-label);
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            margin-right: 10px;
        }

        tbody {
            background-color: #e0e0e0;
            border-radius: 8px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            background-color: #cfcfcf;
            padding: 10px;
            border-radius: 8px;
        }

        .search-bar {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }

        .search-input {
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        #searchInput,
        #turma {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .action-buttons a {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 5px;
            padding: 8px;
            font-size: 12px;
            background-color: #0077b6;
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
        }

        .action-buttons a:hover {
            background-color: #005f8a;
        }

        #status {
            width: 120px;
            padding: 5px;
            border: 2px solid #000;
            border-radius: 4px;
            background-color: #f8f8f8;
            color: #000;
            font-weight: bold;
            cursor: pointer;
        }

        .filter-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
            width: 100%;
        }

        .actions {
            display: inline-flex;
            flex-direction: column;
        }
        .title_logs{
            font-size: 25px;
            color: #000;
        }
    }

</style>

<div class="container_global">

    <!-- Contêiner da Tabela com rolagem horizontal -->
    <div class="table-container">
        <table class="table_logs" id="tableHistorico">
            <h2 class="title_logs">Histórico de Operações</h2>

            <!-- Campo de Filtro Único -->
            <div class="actions">
                <input type="text" id="searchInput" class="search-input" placeholder="Pesquisar..."
                    onkeyup="filtrarHistorico()" />
                 <input type="date" id="searchDataInicialInput" class="search-input"  oninput="filtrarHistorico()" placeholder="Data Inicial"/>
                <input type="date" id="searchDataFinalInput" class="search-input" oninput="filtrarHistorico()"  placeholder="Data Final" />
                <button onclick="imprimirTabela()">Imprimir</button>

                <form class="form-excel" method="POST" action="exportar_excel.php" id="exportForm">
                    <button type="submit">Exportar para Excel</button>
                </form>
            </div>

            <img src="../imagem/senailogo.png" alt="Logo SENAI" class="logo-senai">


            <thead>
                <tr>
                    <th>NI Máquina</th>
                    <th>Nome maquina</th>
                    <th>Aluno</th>
                    <th>Professor</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Tópico</th>
                    <th>Tipo Req</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($historico)): ?>
                    <?php foreach ($historico as $row): ?>
                        <tr>
                            <td data-label="NI Máquina"><?= htmlspecialchars($row["maquina_ni"]) ?></td>
                            <td data-label="nome da maquina"><?= htmlspecialchars($row["tipomaquina_nome"]) ?></td>
                            <td data-label="Aluno"><?= htmlspecialchars($row["aluno_nome"]) ?></td>
                            <td data-label="Professor"><?= htmlspecialchars($row["colaborador_nome"]) ?></td>
                            <td data-label="Data"><?= date('d/m/Y', strtotime($row["historico_data"])) ?></td>
                            <td data-label="Hora"><?= date('H:i', strtotime($row["historico_hora"])) ?></td>
                            <td data-label="Tópico"><?= htmlspecialchars($row["requisito_topico"]) ?></td>
                            <td data-label="Tipo Req"><?= htmlspecialchars($row["tipo_req"]) ?></td>
                        </tr>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Nenhum registro encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div class="pagination">
        <?php
        // Determina os limites das páginas a serem exibidas
        $start_page = max(1, $pagina_atual - 1);
        $end_page = min($total_paginas, $pagina_atual + 1);

        // Botão para a primeira página, se a página inicial exibida não for a primeira página
        if ($start_page > 1) {
            echo '<a href="?pagina=1&filtro=' . urlencode($filtro_unico) . '&filtro_data_inicial=' . urlencode($filtro_data_inicial) . '&filtro_data_final=' . urlencode($filtro_data_final) . '">1</a>';
            if ($start_page > 2) {
                echo '<span>...</span>';
            }
        }

        // Links das três páginas em torno da página atual
        for ($i = $start_page; $i <= $end_page; $i++) {
            echo '<a href="?pagina=' . $i . '&filtro=' . urlencode($filtro_unico) . '&filtro_data_inicial=' . urlencode($filtro_data_inicial) . '&filtro_data_final=' . urlencode($filtro_data_final) . '"';
            if ($i == $pagina_atual)
                echo ' class="current-page"';
            echo '>' . $i . '</a>';
        }

        // Botão para a última página, se a página final exibida não for a última página
        if ($end_page < $total_paginas) {
            if ($end_page < $total_paginas - 1) {
                echo '<span>...</span>';
            }
            echo '<a href="?pagina=' . $total_paginas . '&filtro=' . urlencode($filtro_unico) . '&filtro_data_inicial=' . urlencode($filtro_data_inicial) . '&filtro_data_final=' . urlencode($filtro_data_final) . '">' . $total_paginas . '</a>';
        }

        ?>
    </div>


</div>