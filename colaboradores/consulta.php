<?php
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');

?>
<?php include __DIR__ . '/../sidebar.php'; ?>
<?php
include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador']);

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT 
            c.idcolaborador,
            c.colaborador_nome,
            c.colaborador_nif,
            c.colaborador_email,
            s.setor_nome,
            c.colaborador_status
            FROM colaborador c
            LEFT JOIN setor s ON c.setor_id = s.idsetor
            ORDER BY c.idcolaborador ASC";
    $stmt = $pdo->query($sql);
    $colaboradores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao buscar colaboradores: " . $e->getMessage());
}

if (isset($_SESSION['mensagem'])): ?>
    <div id="successModal" class="modal <?php echo $_SESSION['mensagem_tipo']; ?>">
        <?php echo htmlspecialchars($_SESSION['mensagem']); ?>
    </div>
    <script>
        // Código JavaScript para esconder o modal após 3 segundos
        setTimeout(() => {
            const modal = document.getElementById('successModal');
            if (modal) {
                modal.style.opacity = '0'; // Gradualmente desaparece
                setTimeout(() => modal.remove(), 500); // Remove completamente após 0.5s
            }
        }, 3000); // 3 segundos antes de começar a desaparecer
    </script>
    <?php unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']); ?>
<?php endif; ?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        /* Estilos do cabeçalho, tabela, modal e elementos de busca e filtro */
        h1 {
            text-align: center;
            margin-top: 0;
        }

        table {
            border-collapse: collapse;
            margin: 20px 0;
        }

        .modal {
            padding: 15px;
            color: #fff;
            background-color: #4caf50;
            border-radius: 5px;
            position: fixed;
            top: 850px;
            z-index: 1000;
        }

        .modal.erro {
            background-color: #f44336;
        }

        .search-form {
            align-items: center;
        }

        #searchInput {
            border: none;
            width: 400px;
            padding: 10px;
        }

        .button-container {
            text-align: center;
            margin-bottom: 1rem;
        }

        .search-select {
            padding: 5px;
            border: 2px solid #000000;
            border-radius: 4px;
            background-color: #f8f8f8;
            color: #000000;
            cursor: pointer;
            font-weight: bold;
            min-width: 100px;
            width: 10%;
            margin-left: 15px;
        }

        /* Estilos da tabela e botões de ação */
        th,
        td {
            padding: 15px;
            text-align: center;

        }

        .action-buttons a {
            display: flex;
            align-items: center;
            gap: 7px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            background-color: #a80813;
        }

        .pagination-controls {
            text-align: center;
            margin: 10px;
        }

        .pagination-button {
            margin: 0 5px;
            padding: 5px 10px;
            cursor: pointer;
            border: none;
        }

        .pagination-button.active {
            background-color: dimgray;
            color: white;
        }

        .search-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        .search-input {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 250px;
            max-width: 100%;
        }

        .todos_btns {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .reset-password-btn {
            margin-top: 10px;
            /* Adiciona espaçamento entre os botões */
            text-align: center;
            /* Centraliza o botão */
        }

        .reset-password-btn a {
            background-color: #b22222;
            padding: 10px;
            border-radius: 4px;
            width: 190px;
            color: white;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }

        .action-buttons {
            display: flex;
            flex-direction: row;
        }

        @media (max-width: 968px) {
            table {
                width: 30%;
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

            .reset-password-btn a {
                width: 100px;
            }

            .table-container {
                display: flex;
                justify-content: center;
            }
        }

        /* Responsividade para dispositivos móveis */
        @media (max-width: 685px) {
            .table-container {
                display: flex;
                justify-content: center;
                padding: 0 5px;
                width: 100%;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
                padding: 5px 8px;
            }

            #colaboradorTable {
                width: 96%;
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

            form {
                display: flex;
                flex-direction: column;
                gap: 1px;
                align-items: flex-start;
                width: 95%;
                /* Ajusta o formulário para ocupar toda a largura */
                background-color: #cfcfcf;
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

            .reset-password-btn a {
                width: 120px;
            }

            #searchInput {
                width: 300px;
            }
        }
    </style>
</head>

<body>
    <div class="container_consultar_req">
        <form class="search-form">
            <h1>Funcionários Cadastrados</h1>
            <div class="search-bar consultafuncionario">
                <input type="text" id="searchInput" class="search-input consultafuncionario"
                    placeholder="Pesquisar por Nome ou NIF" onkeyup="filterTable()">
                <select id="statusFilter" class="search-select" onchange="filterTable()">
                    <option value="">Todos</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                </select>
            </div>
        </form>

        <!-- Mensagem "Nenhum funcionário encontrado" -->
        <div id="noResultsMessage"
            style="display: none; text-align: center; color: #f44336; font-weight: bold; padding: 10px;">
            Nenhum funcionário encontrado
        </div>

        <div class="table-container">
            <table id="colaboradorTable">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>NIF</th>
                        <th>Email</th>
                        <th>Setor</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="colaboradorTableBody">
                    <?php if (!empty($colaboradores)): ?>
                        <?php foreach ($colaboradores as $colaborador): ?>
                            <tr>
                                <td data-label="Nome"><?= htmlspecialchars($colaborador['colaborador_nome']) ?></td>
                                <td data-label="NIF"><?= htmlspecialchars($colaborador['colaborador_nif']) ?></td>
                                <td data-label="Email"><?= htmlspecialchars($colaborador['colaborador_email']) ?></td>
                                <td data-label="Setor"><?= htmlspecialchars($colaborador['setor_nome']) ?></td>
                                <td data-label="Status"><?= htmlspecialchars($colaborador['colaborador_status']) ?></td>
                                <td data-label="Ações">
                                    <div class="todos_btns">
                                        <div class="action-buttons">
                                            <a href="toggle_status.php?id=<?= $colaborador['idcolaborador'] ?>"
                                                class="btn-inativar">
                                                <?php if ($colaborador['colaborador_status'] === 'Ativo'): ?>
                                                    <i class="fa fa-times"></i> Desativar
                                                <?php else: ?>
                                                    <i class="fa fa-check"></i> Ativar
                                                <?php endif; ?>
                                            </a>
                                            <a href="alterar.php?id=<?= $colaborador['idcolaborador'] ?>" class="btn-editar">
                                                <i class="fas fa-pencil-alt"></i> Editar
                                            </a>
                                            <a href="excluir.php?id=<?= $colaborador['idcolaborador'] ?>" class="btn-excluir">
                                                <i class="fas fa-trash-alt"></i> Excluir
                                            </a>
                                        </div>
                                        <div class="reset-password-btn">
                                            <a href="resetasenha.php?id=<?= $colaborador['idcolaborador'] ?>"
                                                class="btn-reiniciar">
                                                <i class="fa-solid fa-rotate-right"></i> Redefinir senha
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div id="paginationControls" class="pagination-controls"></div>
    </div>

    <script>
        let rowsPerPage = 10;
        let currentPage = 1;
        let rows = [];
        let filteredRows = [];

        function updatePagination() {
            const paginationControls = document.getElementById("paginationControls");
            paginationControls.innerHTML = ''; // Limpa os controles de paginação

            const totalPages = Math.ceil((filteredRows.length > 0 ? filteredRows.length : rows.length) / rowsPerPage);
            if (totalPages <= 1) return; // Não exibe paginação se houver apenas uma página

            const pageButtons = [];
            const visibleRange = 1; // Quantas páginas antes e depois da atual são exibidas

            for (let i = 1; i <= totalPages; i++) {
                if (
                    i === 1 || // Sempre exibe a primeira página
                    i === totalPages || // Sempre exibe a última página
                    (i >= currentPage - visibleRange && i <= currentPage + visibleRange) // Exibe as páginas próximas da atual
                ) {
                    pageButtons.push(i);
                }
            }

            pageButtons.forEach((page) => {
                const button = document.createElement('button');
                button.innerText = page;
                button.classList.add('pagination-button');

                if (page === currentPage) {
                    button.classList.add('active');
                }

                button.addEventListener('click', () => {
                    currentPage = page;
                    displayTable(currentPage);
                    updatePagination();
                });

                paginationControls.appendChild(button);
            });
        }

        function filterTable() {
            const input = document.getElementById("searchInput").value.toUpperCase();
            const statusFilter = document.getElementById("statusFilter").value.toUpperCase();

            // Filtra as linhas com base na pesquisa e no status
            filteredRows = rows.filter(row => {
                const nome = row.cells[0].innerText.toUpperCase();
                const nif = row.cells[1].innerText.toUpperCase();
                const status = row.cells[4].innerText.toUpperCase(); // Pega o texto do status

                const searchMatch = nome.includes(input) || nif.includes(input); // Nome ou NIF devem corresponder
                const statusMatch = statusFilter === "" || status === statusFilter; // Selecione "Todos" ou corresponda o status

                return searchMatch && statusMatch;
            });

            const noResultsMessage = document.getElementById("noResultsMessage");

            // Exibe a mensagem "Nenhum funcionário encontrado" se não houver resultados
            if (filteredRows.length === 0) {
                document.getElementById("colaboradorTableBody").innerHTML = ''; // Limpa a tabela
                noResultsMessage.style.display = "block"; // Exibe a mensagem de nenhum resultado
            } else {
                noResultsMessage.style.display = "none"; // Esconde a mensagem de nenhum resultado
                displayTable(currentPage); // Exibe os dados filtrados
            }

            // Atualiza a tabela e a paginação
            currentPage = 1; // Reseta para a primeira página após filtrar
            updatePagination();
        }


        function displayTable(page) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const rowsToDisplay = filteredRows.length > 0 ? filteredRows : rows; // Exibe as linhas filtradas ou as originais

            // Limpa a tabela e exibe apenas as linhas da página atual
            document.getElementById("colaboradorTableBody").innerHTML = '';
            rowsToDisplay.slice(start, end).forEach(row => document.getElementById("colaboradorTableBody").appendChild(row));
        }

        window.onload = function () {
            rows = Array.from(document.querySelectorAll("#colaboradorTableBody tr"));
            displayTable(currentPage); // Exibe a página inicial
            updatePagination(); // Atualiza a paginação
        };


    </script>
</body>

</html>