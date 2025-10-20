<?php
// Verifica o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');

// Inclui a conexão com o banco de dados
include '../conexao.php';
include __DIR__ . '/../sidebar.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Atualiza status das máquinas com data de próxima manutenção passada
    $sqlUpdate = "
            UPDATE maquina
            SET maquina_status = 'Inativo'
            WHERE data_proxima_manutencao < CURDATE() AND maquina_status = 'Ativo'
        ";
    $pdo->exec($sqlUpdate);

    // Consulta as máquinas sem limites
    $sql = "
            SELECT 
                m.idmaquina,
                m.maquina_ni,
                tm.tipomaquina_nome,
                m.maquina_ano,
                m.data_proxima_manutencao,
                m.maquina_status
            FROM 
                maquina m
            JOIN 
                tipomaquina tm ON m.tipomaquina_id = tm.idtipomaquina
        ";
    $maquinas_completas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Falha na conexão: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Centralizar o conteúdo da página */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: auto;
            margin: 0;
            background-color: #cfcfcf;
        }

        /* Estilo para links de paginação */
        .pagination a.active {
            background-color: dimgray;
            color: white;
            /* Cor do texto do botão ativo */
        }



        /* Estilo do contêiner da tabela */
        .table-container {
            width: 70%;
            max-height: 70vh;
            margin: 0 auto;
            background-color: transparent;
            border: none;
            padding: 40px;
            border-radius: 8px;
        }

        /* Estilos para o título */
        h1 {
            color: #333;
            text-align: center;
            font-size: 1.8em;
            /* Ajustado conforme o modelo */
            margin-top: 20px;
            /* Adicionando margem superior para descer o título */
            margin-bottom: -50px;
            /* Adicionando margem inferior para dar espaço ao conteúdo abaixo */
        }

        /* Barra de busca centralizada */
        .search-bar {
            display: flex;
            justify-content: center;
            gap: 20px;
        }


        /* Estilo da tabela */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: center;
            font-size: 14px;
            padding: 8px;
        }

        th {
            background-color: #b30000;
            color: white;
        }

        /* Estilo da paginação */
        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination a {
            display: inline-block;
            margin: 0 5px;
            padding: 8px 12px;
            background-color: white;
            color: black;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .pagination a:hover {
            background-color: #999999;
        }

        /* Estilo do filtro de status */
        .search-select {
            padding: 5px;
            border: 2px solid #000000;
            border-radius: 4px;
            background-color: #f8f8f8;
            color: #000000;
            cursor: pointer;
            font-weight: bold;
            min-width: 100px;
            width: 10% !important;
        }

        /* Estilos personalizados para opções Ativo e Inativo */
        .search-select option {
            color: #000000;
        }


        /* Adicione este estilo ao seu CSS existente */
        @media (max-width: 768px) {
            .table-container {
                width: 100%;
                /* Use a largura total em dispositivos menores */
                padding: 10px 20px;
                /* Diminua o padding para melhor uso do espaço */
            }

            .pagination {
                margin-top: 40px;
                /* Espaçamento menor acima da paginação */
                text-align: center;
                /* Centraliza a paginação */
            }

        }


        @media (max-width: 480px) {
            #searchInput {
                width: 250px;
            }

            .search-bar {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }

            form {
                width: 0;
            }

            .search-select {
                width: 110px;
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

            body {
                margin-top: 45px;
            }

            h1 {
                font-size: 24px;
            }


        }
    </style>
</head>

<body>
    <h1>Próximas Manutenções</h1>
    <div class="table-container">
        <form class="search-form">
            <div class="search-bar">
                <input class="search-input" type="text" id="searchInput" placeholder="Pesquisar por Nome da máquina"
                    onkeyup="filtrarTabela()" />
                <select id="statusFilter" class="search-select" onchange="filtrarTabela()">
                    <option value="">Todos</option>
                    <option value="ativo" class="ativo-option">Ativo</option>
                    <option value="inativo" class="inativo-option">Inativo</option>
                </select>
            </div>
        </form>
        <table id="tabelaMaquinas">
            <thead>
                <tr>
                    <th>NI da Máquina</th>
                    <th>Tipo de Máquina</th>
                    <th>Ano de Fabricação</th>
                    <th>Próxima Manutenção</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <!-- Dados da tabela preenchidos pelo JavaScript -->
            </tbody>
        </table>
        <div class="pagination" id="pagination"></div>
    </div>


    <script>
        // Passa os dados PHP para o JavaScript
        const todasMaquinas = <?= json_encode($maquinas_completas) ?>;
        const porPagina = 10;
        let paginaAtual = 1;

        // Função para filtrar e exibir dados na tabela
        function filtrarTabela() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const table = document.getElementById('tabelaMaquinas');
            const tbody = table.getElementsByTagName('tbody')[0];
            tbody.innerHTML = '';

            let maquinasFiltradas = todasMaquinas.filter(maquina => {
                const nome = maquina.maquina_ni ? maquina.maquina_ni.toLowerCase() : '';
                const tipo = maquina.tipomaquina_nome ? maquina.tipomaquina_nome.toLowerCase() : '';
                const ano = maquina.maquina_ano ? maquina.maquina_ano.toString() : '';
                const data = maquina.data_proxima_manutencao ? new Date(maquina.data_proxima_manutencao).toLocaleDateString('pt-BR') : 'N/A';
                const status = maquina.maquina_status ? maquina.maquina_status.toLowerCase() : '';

                return (nome.includes(input) || tipo.includes(input) || ano.includes(input) || data.includes(input) || status.includes(input)) &&
                    (statusFilter === '' || status === statusFilter);
            });

            atualizarPaginacao(maquinasFiltradas.length);

            maquinasFiltradas = maquinasFiltradas.slice((paginaAtual - 1) * porPagina, paginaAtual * porPagina);

            maquinasFiltradas.forEach(maquina => {
                const data = maquina.data_proxima_manutencao ? new Date(maquina.data_proxima_manutencao).toLocaleDateString('pt-BR') : 'N/A';
                const row = `<tr class="${maquina.maquina_status === 'Inativo' ? 'inativo' : ''}">
                        <td data-label='Ni'>${maquina.maquina_ni}</td>
                        <td data-label='Nome'>${maquina.tipomaquina_nome}</td>
                        <td data-label='Ano'>${maquina.maquina_ano}</td>
                        <td data-label='Data'>${data}</td>
                        <td data-label='Status'>${maquina.maquina_status}</td>
                    </tr>`;
                tbody.innerHTML += row;
            });
        }

        // Função para mudar de página
        function mudarPagina(novaPagina) {
            paginaAtual = novaPagina;
            filtrarTabela();
        }

        // Função para atualizar a paginação
        function atualizarPaginacao(totalResultados) {
            const totalPaginas = Math.ceil(totalResultados / porPagina);
            const maxPaginasExibidas = 5; // Limite de botões de página exibidos
            const paginationDiv = document.getElementById('pagination');
            paginationDiv.innerHTML = '';

            const inicioPagina = Math.max(1, paginaAtual - Math.floor(maxPaginasExibidas / 2));
            const fimPagina = Math.min(totalPaginas, inicioPagina + maxPaginasExibidas - 1);

            for (let i = inicioPagina; i <= fimPagina; i++) {
                const link = document.createElement('a');
                link.href = '#';
                link.textContent = i;
                link.className = i === paginaAtual ? 'active' : '';
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    mudarPagina(i);
                });
                paginationDiv.appendChild(link);
            }
        }

        // Inicializa a tabela ao carregar a página
        document.addEventListener('DOMContentLoaded', filtrarTabela);
    </script>
</body>

</html>