<?php
// Configuração da URL base do projeto
// define('BASE_URL', '../../nr12/');

// Inclui a barra lateral e a conexão com o banco de dados
include __DIR__ . '/../sidebar.php';
include '../conexao.php';

try {
    // Conexão com o banco de dados e obtenção dos dados completos dos setores
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "
        SELECT s.idsetor, s.setor_nome, u.unidade_nome, s.setor_status
        FROM setor s
        JOIN unidade u ON s.unidade_id = u.idunidade
    ";
    $setores_completos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Consulta de Setores</title>
    <style>
        form{
            margin: 0!important;
        }
        td {
            border-bottom: none;
        }

        /* Estilos para a tabela */
        .table-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            align-content: center;
        }

        .table-sectors {
            border-collapse: collapse;
        }

        .table-header,
        .table-row {
            background-color: #f9f9f9;
            border: 1px solid #ccc;
        }



        #acoes {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .toggle-link {
            color: #b30000;
            text-decoration: none;
            font-weight: bold;
        }

        .toggle-link:hover {
            color: #800000;
        }

        .edit-button {
            background-color: #a80813;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            text-decoration: none;
            width: 100px;
            height: 30px;
            padding: 6px;
        }

        edit-button:hover {
            background-color: #c82333;
        }

        /* Estilos para a paginação */
        .pagination2 {
            display: flex;
            justify-content: center;
            /* Centraliza a paginação */
            margin: 20px 0;
            flex-wrap: wrap;
            /* Permite que os elementos quebrem linha se necessário */
            gap: 8px;
        }

        .pagination2 a {
            margin: 0 5px;
            padding: 4px 8px;
            background-color: #f2f2f2;
            color: #333;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .pagination2 a.active {
            background-color: dimgray;
            color: white;
        }

        .pagination2 a:hover {
            background-color: gray;
        }

        /* Estilo do filtro de status */
        .search-bar {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 10px;
            align-items: center;
        }

        .search-input {
            padding: 5px;
            /* Reduz o padding para um campo menor */
            border: 2px solid #000000;
            /* Borda preta */
            border-radius: 4px;
            flex-grow: 1;
            /* Permite que o campo de entrada ocupe o espaço restante */
            margin-right: 10px;
            /* Espaçamento à direita do campo de entrada */
            min-width: 200px;
            /* Define uma largura mínima para o campo */
        }

        .search-select {
            padding: 5px;
            /* Reduz o padding para um seletor menor */
            border: 2px solid #000000;
            /* Borda preta */
            background-color: #f8f8f8;
            /* Cor de fundo mais clara */
            color: #000000;
            /* Cor do texto */
            cursor: pointer;
            font-weight: bold;
            /* Aumenta a espessura da fonte */
            min-width: 100px;
            /* Define uma largura mínima para o seletor */
            width: 10% !important;
        }

        @media (max-width: 768px) {
            h1{
                width: 350px;
                display: flex;
                justify-content: center;
                align-items:center ;
            }
            .table-container {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                align-content: center;
            }

            .edit-button {
                width: 100px;
            }

            .search-form {
                max-width: 300px;
            }
        }

        @media (max-width: 560px) {
            .container_consultar_req{
                margin-top: 10%;
            }
            .pesquisa_ativo {
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }

            .edit-button {
                height: 25px;
            }

            .table-container {
                padding: 0 5px;
                width: 70%;
            }

            label {
                margin-top: 10px !important;

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
                padding: 12px;
                border-bottom: 1px solid #ddd;
                transition: background-color 0.3s ease;
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
                display: flex;
                flex-direction: column;
                gap: 1px;
                width: 100%;
                /* Ajusta o formulário para ocupar toda a largura */
                background-color: #cfcfcf;
            }

            /* Alinha o filtro de aluno e de turma na mesma linha */
            .search-bar {
                display: flex;
                flex-direction: column;
                gap: 10px;
                align-items: center;
            }

            #turma {
                width: 100%;
                max-width: 350px;
            }

            .action-buttons {
                flex-direction: row;
            }

            td::before {
                content: attr(data-label);
                font-weight: bold;

            }
            #searchInput{
                width: 290px  !important;
            }
            h1{
                width: 350px;
                display: flex;
                justify-content: center
            };

        }
        table{
            width: auto;
        }


        .table-container {
            margin: 0 auto;
            padding: 40px;
        }

        .table-header-cell,
        .table-cell {
            padding: 10px;
        }

        .search-input,
        .search-select {
            padding: 5px;
            border: 2px solid #000;
            border-radius: 4px;

        }

        .pagination2 {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            gap: 8px;
        }

        .pagination2 a {
            padding: 4px 8px;
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
        }

        .pagination2 a.active {
            background-color: dimgray;
            color: white;
        }

        .pesquisa_ativo {
            display: flex;
            gap: 20px;
        }
    </style>
</head>

<body>
    <div class="container_consultar_req">

        <div class="table-container">
            <!-- Barra de Pesquisa e Filtro -->
            <form class="search-form">
            <h1 class="title">Consulta de Setores</h1>
                <div class="search-bar">         
                   <div class="pesquisa_ativo">
                        <input class="search-input" type="text" id="searchInput"
                            placeholder="Pesquisar por Nome, Unidade" onkeyup="filtrarTabela()" />
                        <select id="statusFilter" class="search-select" onchange="filtrarTabela()">
                            <option value="">Todos</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Tabela de Setores -->
            <table id="tabelaSetores" class="table-sectors">
                <thead>
                    <tr class="table-header">
                        <th class="table-header-cell">Setor</th>
                        <th class="table-header-cell">Unidade</th>
                        <th class="table-header-cell">Status</th>
                        <th class="table-header-cell">Ações</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <!-- Paginação -->
            <div class="pagination2" id="pagination"></div>


            <script>
                // Dados iniciais e variáveis de controle de paginação
                const todosSetores = <?= json_encode($setores_completos) ?>;
                const itensPorPagina = 10;
                let paginaAtual = 1;

                // Função para filtrar e atualizar a tabela
                function filtrarTabela() {
                    const input = document.getElementById('searchInput').value.toLowerCase();
                    const statusFiltro = document.getElementById('statusFilter').value;
                    const tbody = document.getElementById('tabelaSetores').querySelector('tbody');
                    tbody.innerHTML = '';

                    // Filtra os setores conforme os critérios de pesquisa
                    let setoresFiltrados = todosSetores.filter(setor => {
                        const nome = setor.setor_nome.toLowerCase();
                        const unidade = setor.unidade_nome.toLowerCase();
                        const status = setor.setor_status.toLowerCase();

                        let incluiStatus = true;
                        if (statusFiltro === 'ativo') {
                            incluiStatus = (status === 'ativo');
                        } else if (statusFiltro === 'inativo') {
                            incluiStatus = (status === 'inativo' || status === 'desativado');
                        }

                        return (nome.includes(input) || unidade.includes(input)) && incluiStatus;
                    });

                    // Exibe uma mensagem se não houver resultados
                    if (setoresFiltrados.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="4" style="text-align: center;">Nenhum setor encontrado</td></tr>`;
                    } else {
                        atualizarPaginacao(setoresFiltrados.length);
                        setoresFiltrados = setoresFiltrados.slice((paginaAtual - 1) * itensPorPagina, paginaAtual * itensPorPagina);

                        setoresFiltrados.forEach(setor => {
                            const row = `
                        <tr class="table-row">
                            <td data-label="Setor" class="table-cell">${setor.setor_nome}</td>
                            <td data-label="Unidade"class="table-cell">${setor.unidade_nome}</td>
                            <td data-label="Status"class="table-cell">${setor.setor_status}</td>
                            <td data-label="Ações"class="table-cell" id="acoes">
                                <a class="edit-button" href="${setor.setor_status === 'Ativo' ? 'desativar' : 'ativar'}.php?id=${setor.idsetor}">
                                    <i class="fas ${setor.setor_status === 'Ativo' ? 'fa fa-times' : 'fa fa-check'}"></i>
                                    ${setor.setor_status === 'Ativo' ? 'Desativar' : 'Ativar'}
                                </a>
                                <a class="edit-button" href="editar.php?id=${setor.idsetor}">
                                    <i class="fas fa-pencil-alt"></i> Editar
                                </a>
                            </td>
                        </tr>`;
                            tbody.innerHTML += row;
                        });
                    }
                }

                // Função para mudar de página e recarregar a tabela
                function mudarPagina(novaPagina) {
                    paginaAtual = novaPagina;
                    filtrarTabela();
                }

                // Função para atualizar a paginação
                function atualizarPaginacao(totalResultados) {
                    const totalPaginas = Math.ceil(totalResultados / itensPorPagina);
                    const paginacaoDiv = document.getElementById('pagination');
                    paginacaoDiv.innerHTML = '';

                    for (let i = 1; i <= totalPaginas; i++) {
                        const link = document.createElement('a');
                        link.href = '#';
                        link.textContent = i;
                        link.className = i === paginaAtual ? 'active' : '';
                        link.addEventListener('click', (event) => {
                            event.preventDefault();
                            mudarPagina(i);
                        });
                        paginacaoDiv.appendChild(link);
                    }
                }

                // Carrega a tabela inicialmente
                filtrarTabela();
            </script>
</body>
</>

</html>