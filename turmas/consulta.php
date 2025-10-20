<?php
include '../conexao.php';
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');
include __DIR__ . '/../sidebar.php';

// Parâmetros do filtro
$termoPesquisa = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$resultadosPorPagina = 10;
$paginaAtual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset = ($paginaAtual - 1) * $resultadosPorPagina;

// Função para contar o total de resultados
function contarTurmas($pdo, $termoPesquisa, $statusFilter = '')
{
    $sql = "
        SELECT COUNT(*) as total
        FROM turmas t
        LEFT JOIN curso c ON t.curso_id = c.idcurso
        WHERE (t.turma_nome LIKE :termoPesquisa OR c.curso_nome LIKE :termoPesquisa)
    ";

    if (!empty($statusFilter)) {
        $sql .= " AND t.turmas_status = :statusFilter";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':termoPesquisa', '%' . $termoPesquisa . '%', PDO::PARAM_STR);

    if (!empty($statusFilter)) {
        $stmt->bindValue(':statusFilter', $statusFilter, PDO::PARAM_STR);
    }

    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    return $resultado['total'];
}

// Função para buscar turmas
function buscarTurmas($pdo, $termoPesquisa, $offset, $resultadosPorPagina, $statusFilter = '')
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

    if (!empty($statusFilter)) {
        $sql .= " AND t.turmas_status = :statusFilter";
    }

    $sql .= " ORDER BY t.idturmas ASC LIMIT :offset, :resultadosPorPagina";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':termoPesquisa', '%' . $termoPesquisa . '%', PDO::PARAM_STR);

    if (!empty($statusFilter)) {
        $stmt->bindValue(':statusFilter', $statusFilter, PDO::PARAM_STR);
    }

    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':resultadosPorPagina', $resultadosPorPagina, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obter total de turmas e calcular o total de páginas
$totalResultados = contarTurmas($pdo, $termoPesquisa, $statusFilter);
$totalPaginas = ceil($totalResultados / $resultadosPorPagina);

$turmas = buscarTurmas($pdo, $termoPesquisa, $offset, $resultadosPorPagina, $statusFilter);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filtrar Turmas</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container">
        <h1>Turmas Cadastradas</h1>
        <div class="search-bar">
            <form id="filter-form">
                <input type="text" id="searchInput" name="search" class="search-input"
                    placeholder="Pesquisar por Nome ou Curso" value="<?= htmlspecialchars($termoPesquisa) ?>">
                <select name="status" id="statusFilter">
                    <option value="">Todos</option>
                    <option value="Ativo" <?= $statusFilter == 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="Inativo" <?= $statusFilter == 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </form>
        </div>

        <div class="table-container">
            <table id="tabelaturma">
                <thead>
                    <tr>
                        <th>Turma</th>
                        <th>Período</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Nome do Curso</th>
                        <th>Nome do Colaborador</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <!-- Dados das turmas renderizados pelo PHP -->
                    <?php if (count($turmas) > 0): ?>
                        <?php foreach ($turmas as $turma): ?>
                            <tr>
                                <td data-label="Turma"><?= htmlspecialchars($turma['turma_nome']) ?></td>
                                <td data-label="Periodo"><?= htmlspecialchars($turma['turma_periodo']) ?></td>
                                <td data-label="Início"><?= htmlspecialchars($turma['turma_inicio']) ?></td>
                                <td data-label="Fim"><?= htmlspecialchars($turma['turma_fim']) ?></td>
                                <td data-label="Curso"><?= htmlspecialchars($turma['curso_nome']) ?></td>
                                <td data-label="Colaborador"><?= htmlspecialchars($turma['colaborador_nome']) ?></td>
                                <td data-label="Status"><?= htmlspecialchars($turma['turmas_status']) ?></td>
                                <td data-label="Ações">
                                    <div class='botoes'>
                                        <a href="alterar.php?id=<?= $turma['idturmas'] ?>">
                                            <i class="fas fa-pencil-alt"></i> Editar
                                        </a>
                                        <a href="excluir.php?id=<?= $turma['idturmas'] ?>">
                                            <i class="fas fa-trash-alt"></i> Excluir
                                        </a>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Nenhuma turma encontrada</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Navegação de paginação -->
        <div class="pagination-controls">
            <div class="paginacao" id="pagination">
                <?php if ($paginaAtual > 1): ?>
                    <a href="#" data-pagina="<?= $paginaAtual - 1 ?>">Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <a href="#" data-pagina="<?= $i ?>" class="<?= $i == $paginaAtual ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($paginaAtual < $totalPaginas): ?>
                    <a href="#" data-pagina="<?= $paginaAtual + 1 ?>">Próxima</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            function atualizarTabela(pagina = 1) {
                let search = $('#searchInput').val();
                let status = $('#statusFilter').val();

                $.ajax({
                    url: '', // Mesma página
                    method: 'GET',
                    data: { search: search, status: status, pagina: pagina },
                    success: function (response) {
                        // Atualizar tabela e paginação
                        $('#table-body').html($(response).find('#table-body').html());
                        $('#pagination').html($(response).find('#pagination').html());
                    },
                    error: function () {
                        alert('Erro ao carregar dados!');
                    }
                });
            }

            // Filtrar tabela com busca e status
            $('#searchInput').on('input', function () {
                atualizarTabela(1); // Reseta para a primeira página
            });

            $('#statusFilter').on('change', function () {
                atualizarTabela(1);
            });

            // Navegar entre páginas
            $(document).on('click', '#pagination a', function (e) {
                e.preventDefault();
                const pagina = $(this).data('pagina');
                if (pagina) {
                    atualizarTabela(pagina);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            function atualizarTabela() {
                let search = $('#searchInput').val();
                let status = $('#statusFilter').val();

                $.ajax({
                    url: '', // Mesma página
                    method: 'GET',
                    data: { search: search, status: status },
                    success: function (response) {
                        let novaTabela = $(response).find('#table-container').html();
                        $('#table-container').html(novaTabela);
                    },
                    error: function () {
                        alert('Erro ao buscar os dados.');
                    }
                });
            }

            $('#searchInput').on('input', function () {
                atualizarTabela();
            });

            $('#statusFilter').on('change', function () {
                atualizarTabela();
            });
        });
    </script>

    <!-- A estilização original do seu código já deve estar aqui -->
</body>

</html>
</div>
</div>


</div>
<script>
    // Função que filtra os resultados da tabela
    function filtrarNaTabela() {
        const termoPesquisa = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
        const linhas = document.querySelectorAll('#tabelaturma tbody tr');

        linhas.forEach(linha => {
            let mostraLinha = true;

            // Verificar se o termo de pesquisa corresponde a qualquer célula da linha
            const nomeTurma = linha.cells[0].textContent.toLowerCase();
            const cursoNome = linha.cells[4].textContent.toLowerCase();
            const status = linha.cells[6].textContent.toLowerCase();

            // Verificar se o nome da turma ou o nome do curso corresponde ao termo de pesquisa
            if (termoPesquisa && !nomeTurma.includes(termoPesquisa) && !cursoNome.includes(termoPesquisa)) {
                mostraLinha = false;
            }

            // Verificar se o filtro de status corresponde
            if (statusFilter && status !== statusFilter) {
                mostraLinha = false;
            }

            // Exibir ou ocultar a linha com base nos filtros
            linha.style.display = mostraLinha ? '' : 'none';
        });
    }

</script>


</body>
<style>
    body {
        margin: revert;
    }

    .paginacao {
        margin: 20px auto;
        text-align: center;
        padding-bottom: 40px;
        cursor: pointer;
        align-items: center;
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .paginacao a {
        text-decoration: none;
        padding: 6px 9px !important;
        color: black;
        transition: background-color 0.3s;
        background-color: #fff;
        border-radius: 1px !important;
        font-weight: bold;
        align-items: center;
        text-align: center;
    }

    .paginacao a:hover {
        background-color: #b22222;
        color: whitesmoke;
    }

    .paginacao .pagina-atual {
        background-color: dimgray !important;
        color: white !important;
        padding: 6px 9px !important;
        margin: 0 5px;
        border-radius: 1px !important;
    }


    /* Contêiner de controles de paginação abaixo da tabela */
    #paginationControls {
        display: flex;
        justify-content: center;
        margin-top: 20px;
        margin-bottom: 100px;
        /* Espaçamento entre a tabela e os controles de paginação */
        align-items: center;
    }

    /* Estilo dos botões de paginação */
    .pagination-button {
        padding: 8px 12px;
        margin: 0 5px;
        text-decoration: none;
        color: black;
        background-color: #fff;
        border: 1px solid #ddd;
        cursor: pointer;
    }

    .pagination-button:hover {
        background-color: #f0f0f0;
    }



    #modalBackground {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        /* Fundo escurecido */
        z-index: 9998;
        /* Faz com que o fundo fique atrás do modal */
        display: none;
        /* Inicialmente escondido */
    }

    /* O modal, com opacidade no fundo */
    #modalAlunos {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        /* Move o modal para o centro da tela */
        width: 80%;
        max-width: 1200px;
        /* Largura máxima do modal */
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        /* Garante que o modal fique acima do fundo */
        padding: 20px;
        border-radius: 8px;
        /* Bordas arredondadas */
        background-color: rgba(255, 255, 255, 1);
        /* Fundo branco do modal */
        border: solid 2px #B22222;
    }

    form {
        display: contents;
    }

    select {
        width: 5%;
        padding: 5px;
        border: 2px solid #000000;
        border-radius: 4px;
        background-color: #f8f8f8;
        color: #000000;
        cursor: pointer;
        font-weight: bold;
        min-width: 100px;
    }

    #modalAlunos>div {
        background-color: #fff;
        width: 100%;
        max-height: 70vh;
        /* Limita a altura do modal */
        overflow-y: auto;
        /* Permite rolagem se o conteúdo for grande */
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        text-align: center;
    }

    #modalAlunos table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0px;
        border: 1px solid #ddd;
    }

    #modalAlunos th,
    #modalAlunos td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    #modalAlunos th {
        background-color: #B22222;
        color: white;
    }

    /* Limitar a altura do conteúdo dos alunos e permitir rolagem */
    #conteudoAlunos {
        max-height: 200px;
        /* Limita a altura do corpo da tabela de alunos */
        overflow-y: auto;
        /* Permite rolagem vertical */
    }

    button {
        padding: 10px 20px;
        background-color: #B22222;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-top: 20px;
    }

    .table-responsive {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    button:hover {
        background-color: #c1000a;
    }


    /* Centralização do container principal */
    .container_consultar_req {
        display: flex;
        flex-direction: column;
        align-items: center;
        /* Centraliza horizontalmente */
        justify-content: center;
        /* Centraliza verticalmente */
        height: 100vh;
        /* Preenche a altura total da janela */
        padding: 20px;
        /* Espaçamento interno */
        box-sizing: border-box;
    }

    /* Ajustes para a tabela */


    /* Centralização do título */
    h1 {
        color: #333;
        text-align: center;
        font-size: 1.8em;
    }

    /* Barra de busca centralizada */
    .search-bar {
        display: flex;
        justify-content: center;
        gap: 20px;
    }

    .search-input {
        border: 1px solid #ddd;
        padding: 10px;
        font-size: 1em;
        border-radius: 4px;
        width: 300px;
        /* Define um tamanho fixo para a barra de pesquisa */
        max-width: 100%;
    }

    form input [type="text"] {
        padding: 8px 12px;

    }

    /* Estilo da tabela centralizada */


    table {
        width: 100%;
        /* Faz com que a tabela ocupe toda a largura disponível */
        border-collapse: collapse;
    }

    th,
    td {
        font-size: 13px;
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #b22222;
    }

    /* Ajuste dos botões da tabela */
    .botoes {
        display: flex;
        justify-content: space-evenly;
    }

    .botoes button,
    .botoes a {
        display: flex;
        justify-content: center;
        gap: 5px;
        width: 100px;
        padding: 8px 2px;
        margin: 0 5px;
        font-weight: bold;
        text-decoration: none;
        border-radius: 4px;
        color: white;
        background-color: #B22222;
        border: 1px solid #333;
        transition: background-color 0.3s ease;
    }

    .botoes button:hover,
    .botoes a:hover {
        background-color: #c1000a;
    }



    #modalAlunos h2 {
        text-align: center;
        font-size: 1.5em;
    }

    /* Responsividade para telas pequenas */
    @media (max-width: 680px) {
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

    /* Estilos gerais da tabela */


    .pagination {
        margin: 10px auto;
        padding-bottom: 20px;
    }

    .pagination a,
    .pagination .pagina-atual {
        padding: 8px 12px;
        margin: 0 3px;
        border-radius: 4px;
        transition: background-color 0.3s;
    }

    .pagination .pagina-atual {
        font-weight: bold;
        background-color: #a0a0a0;
        color: white;
    }
    form input[type="text"]:focus{
        border-color: none;
    }
</style>

</html>