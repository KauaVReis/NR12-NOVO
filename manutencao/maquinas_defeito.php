<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');
include __DIR__ . '/../sidebar.php';
include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador', 'Professor']);

// Função para buscar registros com paginação
function buscarRegistros($pdo, $search = '', $limit = 3, $offset = 0)
{
    $sql = "SELECT d.id, d.descricao, d.data_registro, d.colaborador_id, d.aluno_id, d.maquina_id, 
                    c.colaborador_nome, a.aluno_nome, m.maquina_ni AS maquina_ni
                FROM defeitos d
                JOIN colaborador c ON d.colaborador_id = c.idcolaborador
                JOIN aluno a ON d.aluno_id = a.idaluno
                JOIN maquina m ON d.maquina_id = m.idmaquina
                WHERE (a.aluno_nome LIKE :search OR m.maquina_ni LIKE :search)
                ORDER BY d.data_registro ASC
                LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para contar o número total de registros para paginação
function contarRegistros($pdo, $search = '')
{
    $sql = "SELECT COUNT(*) FROM defeitos d
                JOIN colaborador c ON d.colaborador_id = c.idcolaborador
                JOIN aluno a ON d.aluno_id = a.idaluno
                JOIN maquina m ON d.maquina_id = m.idmaquina
                WHERE (a.aluno_nome LIKE :search OR m.maquina_ni LIKE :search)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Parâmetros de busca e página atual
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Obter registros e contagem total
$registros = buscarRegistros($pdo, $search, $limit, $offset);
$totalRegistros = contarRegistros($pdo, $search);
$totalPaginas = ceil($totalRegistros / $limit);

// Verifica se é uma requisição AJAX
if (isset($_GET['ajax'])) {
    $registros = buscarRegistros($pdo, $search, $limit, $offset); // Sem limite ao filtrar

    if (empty($registros)) {
        echo '<tr><td colspan="7">Nenhum registro encontrado.</td></tr>';
    } else {
        foreach ($registros as $registro) {
            echo '<tr>';
            echo '<td data-label="descricao">' . htmlspecialchars($registro['descricao']) . '</td>';
            echo '<td data-label="colaborador">' . htmlspecialchars($registro['colaborador_nome']) . '</td>';
            echo '<td data-label="aluno">' . htmlspecialchars($registro['aluno_nome']) . '</td>';
            echo '<td data-label="maquina">' . htmlspecialchars($registro['maquina_ni']) . '</td>';
            echo '<td data-label="data">' . htmlspecialchars($registro['data_registro']) . '</td>';
            echo '<td data-label="Ação"><a href="cadastrar.php?id_defeito=' . htmlspecialchars($registro['id']) . '&maquina_ni=' . htmlspecialchars($registro['maquina_ni']) . '" class="btn-marcacao">Marcar Manutenção</a></td>';
            echo '</tr>';
        }
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Defeitos Registrados</title>
    <style>
        .ConsultaMaquinas {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin: auto;
        }

        h1 {
            margin-bottom: 20px;
        }

        #filtroFormAluno {
            box-shadow: none;
            border: none;
            background-color: #cfcfcf;
            margin: 0%;
            transition: none;
        }

        #turma {
            background-color: #fff;
            border: none;
        }

        .caixa {
            max-width: 800px;
        }

        .search-bar {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .search-input {
            padding: 10px;
            width: 100%;
            max-width: 400px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        body {
            height: auto;
        }

        .table-container {
            width: 50%;
            /* A largura vai ocupar toda a largura disponível */
            border-collapse: collapse;
        }

        table {
            width: 50%;
            /* Certifique-se de que a tabela ocupe toda a largura do contêiner */
        }

        th,
        td {
            padding: 10px;

            white-space: nowrap;

        }

        /* Responsividade - Quando a tela for menor que 768px */


        .action-buttons a {
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            margin: 0 2px;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background-color: #a80813;
        }

        .btn-inativar:hover,
        .btn-editar:hover,
        .btn-excluir:hover {
            background-color: #c82333;
        }

        #search {
            padding: 5px;
            /* Aumenta o padding para tornar o campo de busca mais fácil de interagir */
            font-size: 1.2em;
            /* Aumenta o tamanho da fonte para melhorar a legibilidade */
            width: 25%;
            /* Faz o campo de busca ocupar toda a largura disponível */
            margin-top: 5px;
            /* Ajusta a margem superior para dar mais espaço ao filtro */
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a {
            text-decoration: none;
            padding: 4px 8px;
            color: black;
            margin: 0 3px;
            transition: background-color 0.3s;
            background-color: #fff;
        }

        .pagination a:hover {
            background-color: gray;
            font-weight: bold;
        }

        .pagination a.active {
            background-color: dimgray;
            color: white;
        }

        /* Estilos para o botão de "Marcar Manutenção" */
        .btn-marcacao {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #b30000;
            /* Vermelho */
            color: white;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            text-align: center;
            transition: background-color 0.3s, transform 0.2s ease;
        }

        .btn-marcacao:active {
            background-color: #bd2130;
            /* Quando clicado */
        }

        /* Estilo de foco para acessibilidade */
        .btn-marcacao:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.5);
            /* Foco visível com cor vermelha */
        }

        th {
            font-size: 14px;
        }



        /* Ajuste para dispositivos menores (como o modelo específico Xiaomi Redmi Pad SE) */
        @media (max-width: 908px) {
            .table-container {
                width: 55%;

            }

            th,
            td {
                padding: 10px;
                white-space: break-spaces;
            }

            .action-buttons {
                display: flex;
                flex-direction: column;
                /* Para que fiquem um embaixo do outro */
                gap: 8px;
                /* Espaço entre os botões */
                align-items: center;
                /* Centraliza os botões na célula */
            }

            .btn-editar,
            .btn-inativar {
                padding: 8px 16px;
                /* Aumenta o espaço interno dos botões */
                font-size: 0.9em;
                width: 100%;
                /* Botões ocupam a largura total do contêiner */
                text-align: center;
                /* Centraliza o texto */
                border: none;
                /* Remove bordas padrão */
                border-radius: 5px;
                /* Bordas arredondadas para uma estética mais moderna */
                color: #fff;
                cursor: pointer;
                /* Indicador de clique */
                transition: background-color 0.3s ease;
                /* Transição suave */
            }

            /* Estilos de hover para feedback visual */
            .btn-editar:hover,
            .btn-inativar:hover {
                background-color: #a93226;
                /* Cor de fundo um pouco mais escura ao passar o mouse */
            }

        }

        /* Responsividade para dispositivos móveis */
        @media (max-width: 550px) {
           
            .table-container {
                width: 100%;
                padding: 5px 5px;
                margin: 40px 0;

            }

            .search-input {
                padding: 15px;
                /* Aumenta o padding para tornar o campo de busca mais fácil de interagir */
                font-size: 1.2em;
                /* Aumenta o tamanho da fonte para melhorar a legibilidade */
                width: 70%;
                /* Faz o campo de busca ocupar toda a largura disponível */
                margin-top: 10px;
                /* Ajusta a margem superior para dar mais espaço ao filtro */
                border: 1px solid #ccc;
                border-radius: 5px;
            }

            /* Alterando o estilo do campo de busca no formulário */
            #search {
                padding: 5px;
                width: 70%;
                /* Ocupa toda a largura disponível da tela */
                font-size: 1.2em;
                /* Aumenta o tamanho da fonte */
                margin-top: 10px;
                /* Adiciona um pouco de margem no topo */
                border: 1px solid #ccc;
                border-radius: 5px;
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
                font-size: 0.7em;
                padding: 5px;
            }

            tr {
                border-bottom: 1px solid #ddd;
                padding-bottom: 8px;
                margin-bottom: 8px;
                background-color: #f8f8f8;
                border-radius: 6px;
                box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.1);
                margin-top: ;
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

            form {
                background-color: #cfcfcf;
            }

            .btn-marcacao {
                padding: 10px 5px;
                font-size: 10px;
            }

            h1 {
                font-size: 20px;
            }

            body {
                margin-top: 65px;
            }

            .requisitos_especificos {
                width: 250px;
                margin-top: -600px !important;
            }

        }


        .btn-novo {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #007bff;
            /* Azul */
            color: white;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            text-align: center;
            transition: background-color 0.3s, transform 0.2s ease;
            font-size: 16px;
            margin-left: 5px;
            /* Espaço entre os botões */
        }

        .btn-novo:hover {
            background-color: #0056b3;
            /* Azul mais escuro */
            transform: scale(1.05);
        }

        .btn-novo:active {
            background-color: #004085;
            /* Azul mais escuro ao clicar */
        }
        
    </style>
</head>

<body>
    <div class="ConsultaMaquinas">
        <h1>Relatório de Manutenção por Aluno</h1>

        <!-- Campo de busca -->
        <input type="text" id="search" placeholder="Buscar por aluno ou NI da máquina"
            value="<?= htmlspecialchars($search) ?>" onkeyup="filtrarTabela()">

        <table id="alunosTable" class="table-container">
            <thead>
                <tr>

                    <th>Descrição</th>
                    <th>Colaborador</th>
                    <th>Aluno</th>
                    <th>NI da Máquina</th>
                    <th>Data Registro</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody id="tabela-corpo">
                <?php
                foreach ($registros as $registro) {
                    echo '<tr>';

                    echo '<td data-label="descrição">' . htmlspecialchars($registro['descricao']) . '</td>';
                    echo '<td data-label="colaborador">' . htmlspecialchars($registro['colaborador_nome']) . '</td>';
                    echo '<td data-label="aluno">' . htmlspecialchars($registro['aluno_nome']) . '</td>';
                    echo '<td data-label="ni">' . htmlspecialchars($registro['maquina_ni']) . '</td>';
                    echo '<td data-label="data">' . htmlspecialchars($registro['data_registro']) . '</td>';
                    echo '<td data-label="Ação">';
                    echo '<a href="cadastrar.php?id_defeito=' . htmlspecialchars($registro['id']) . '&maquina_ni=' . htmlspecialchars($registro['maquina_ni']) . '" class="btn-marcacao">Marcar Manutenção</a>';
                    echo ' ';
                    echo '<a href="javascript:void(0);" onclick="abrirModal(\'' . htmlspecialchars($registro['maquina_ni']) . '\')" class="btn-marcacao">Requisitos com defeito</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>

        <!-- Paginação -->
        <div class="pagination">
            <?php
            $previousPage = $page - 1;
            $nextPage = $page + 1;

            // Botão "Anterior"
            if ($page > 1) {
                echo "<a href='?page=$previousPage&search=" . urlencode($search) . "'>« Anterior</a>";
            }

            // Exibe os números de páginas
            for ($i = 1; $i <= $totalPaginas; $i++) {
                $activeClass = ($i == $page) ? 'active' : ''; // Verifica se é a página atual
                echo "<a href='?page=$i&search=" . urlencode($search) . "' class='$activeClass'>$i</a>";
            }

            // Botão "Próximo"
            if ($page < $totalPaginas) {
                echo "<a href='?page=$nextPage&search=" . urlencode($search) . "'>Próximo »</a>";
            }


            ?>
        </div>
        <div id="modal" style="display: none;">
            <div class="requisitos_especificos"
                style="background-color: #ffdddd; padding: 20px;  border-radius: 8px; border: 2px solid #a80813; max-width: 400px; margin-top: -300px;">

                <div id="requisitosContent" style="color: #a80813; font-size: 16px; margin-bottom: 20px;"></div>
                <button onclick="fecharModal()"
                    style="background-color: #a80813; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Fechar</button>
            </div>
        </div>
    </div>


    <script>
        function abrirModal(maquinaNi) {
            fetch('buscar_requisito.php?maquina_ni=' + maquinaNi)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('requisitosContent').innerHTML = data;
                    document.getElementById('modal').style.display = 'block';
                })
                .catch(error => console.error('Erro:', error));
        }

        function fecharModal() {
            document.getElementById('modal').style.display = 'none';
        }
    </script>

    <script>


        let timeoutId;
        function filtrarTabela(page = 1) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                let xhr = new XMLHttpRequest();
                let search = document.getElementById('search').value;

                xhr.open('GET', '?ajax=1&search=' + encodeURIComponent(search) + '&page=' + page, true);

                xhr.onload = function () {
                    if (this.status == 200) {
                        // Atualiza o corpo da tabela com os registros da página atual
                        document.getElementById('tabela-corpo').innerHTML = this.responseText;

                        // Ajusta o estilo das células da tabela
                        document.querySelectorAll('.table-container td').forEach(function (td) {
                            td.style.padding = '5px';
                            td.style.fontSize = '0.8em';
                        });

                        // Se a altura máxima for atingida, a rolagem será ativada automaticamente
                        aplicarEstilosResponsivos();
                    }
                };

                xhr.send();
            }, 300);
        }

        // Função para aplicar estilos responsivos
        function aplicarEstilosResponsivos() {
            if (window.innerWidth <= 768) {
                document.querySelectorAll('.table-container td').forEach(function (td) {
                    td.style.padding = '5px';
                    td.style.fontSize = '0.8em';
                });
            }
        }
    </script>

</body>

</html>