<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);

// Adiciona uma barra no final se não houver
$base_dir = rtrim($base_dir, '/') . '/';

// Corrige a URL para sempre começar do diretório raiz do projeto
// define('BASE_URL', '../../nr12/');
?>
<?php include __DIR__ . '/../sidebar.php'; ?>

<?php
// Incluindo a conexão com o banco de dados
include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador', 'Professor']);

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifique o status selecionado pelo usuário
    $statusSelecionado = isset($_GET['status']) ? $_GET['status'] : '';

    // Modifique a consulta SQL para incluir o filtro de status, se aplicável
    $sql = "SELECT 
            r.idrequisitos,
            r.tipo_req,
            r.requisito_topico,
            r.requisitos_status
            FROM requisitos r";

    if ($statusSelecionado !== '') {
        $sql .= " WHERE r.requisitos_status = :status";
    }

    $sql .= " ORDER BY r.idrequisitos ASC";

    $stmt = $pdo->prepare($sql);

    if ($statusSelecionado !== '') {
        $stmt->bindParam(':status', $statusSelecionado, PDO::PARAM_STR);
    }

    $stmt->execute();
    $requisitos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $mapaTipoReq = [
        "seguranca" => "segurança",
    ];
} catch (PDOException $e) {
    die("Erro ao buscar requisitos: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilos.css">

    <style>
        tr {
            font-size: 16px;
        }

        table {
            width: 100%;
            max-width: 1600px;
        }

        .pagination-controls {
            margin-top: 10px;
            text-align: center;
        }

        .pagination-button {
            margin: 0 5px;
            padding: 5px 10px;
            cursor: pointer;
            border: none;
        }

        .pagination-button:hover {
            font-weight: bold;
            background-color: #A9A9A9;
        }

        .pagination-button.active {
            color: white;
            background-color: dimgray;
        }

        .search-bar {
            display: flex;
            gap: 20px;
        }

        .search-input {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 250px;
            max-width: 100%;
        }

        .container_consultar_req h1 {
            text-align: center;
    
        }

        .action-buttons a {
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            margin: 0 5px;
            transition: background-color 0.3s;
        }

        .btn-inativar {
            background-color: #B22222;
            border: 1px solid black;
        }

        .btn-inativar:hover {
            background-color: #c82333;
        }

        .btn-editar {
            background-color: #B22222;
            border: 1px solid black;
        }

        .btn-editar:hover {
            background-color: #dc3545;
        }

        .toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            padding: 15px;
            border-radius: 5px;
            opacity: 0;
            transition: opacity 0.5s, bottom 0.5s;
            z-index: 1000;
            height: 75px;
            text-align: center;
            background-color: #4CAF50;
        }

        .toast.show {
            opacity: 1;
            bottom: 30px;
        }

        .toast.sucesso {
            background-color: #4CAF50;
        }

        .toast.erro {
            background-color: #f44336;
        }

        .search-form {
            margin-bottom: 1.5rem;
            background-color: transparent;
            border: none;
            justify-content: none;
            box-shadow: 0 0px 0px rgba(0, 0, 0, 0.0) !important;
            text-align: center;
            /* Centraliza o formulário de busca */
        }
        form{
            width: auto;
            margin: auto;
        }


        .container_consultar_req {
            margin-top: 40px;
        }

        @media (max-width: 1024px) {
            .table-container {
                overflow-x: auto;
                padding: 0 10px;
            }

            table {
                width: 100%;
                font-size: 0.9em;
            }
        }

        @media (max-width: 768px) {
            table {
                font-size: 0.85em;
            }

            .table-container {
                overflow-x: auto;
                padding: 0 8px;
            }
        }

        @media (max-width: 480px) {
            .container_consultar_req{
                margin-top: 20%;
            }
            .table-container {
                padding: 0 5px;
                overflow-x: auto;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
                width: 100%;
            }

            th,
            td {
                text-align: left;
                font-size: 0.8em;
                padding: 8px 5px;
                box-sizing: border-box;
            }

            thead {
                display: none;
            }

            tr {
                margin-bottom: 8px;
                border-radius: 6px;
                background-color: #f8f8f8;
                box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.1);
            }

            td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px;
                border-bottom: 1px solid #ddd;
            }

            td::before {
                content: attr(data-label);
                font-weight: bold;
                
            };

            }
        
            @media (max-width: 1162px) {
                .action-buttons{
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                }


            }


        @media (max-width: 768px) {

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

        @media (max-width: 480px) {
            .table-container {
                padding: 0 20px;
            }
            #searchInput{
                width: 290px;
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
            }

            td {
                display: flex;
                justify-content: space-between;
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
            table{
                margin-top: 0;
            }
        }

        .status-filter {
            padding: 5px;
            /* Reduz o padding para um seletor menor */
            border: 2px solid #000000;
            /* Borda preta */
            border-radius: 4px;
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
    </style>
</head>

<body>
    <div class="container_consultar_req">
        <h1>Lista de Requisitos</h1>

        <form class="search-form">
            <div class="search-bar consultafuncionario">
                <input type="text" id="searchInput" class="search-input consultafuncionario"
                    placeholder="Pesquisar por Tópico de Requisito" onkeyup="filterTable()">
                <select id="statusFilter" class="status-filter" onchange="filterTable()">
                    <option value="">Todos</option>
                    <option value="Ativo">Ativo</option>
                    <option value="Inativo">Inativo</option>
                </select>
            </div>
        </form>


        <div class="table-container">
            <table id="requisitoTable">
                <thead>
                    <tr>
                        <th>Tipo de Requisição</th>
                        <th>Tópico de Requisito</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requisitos)): ?>
                        <?php foreach ($requisitos as $requisito): ?>
                            <tr>
                                <td data-label="Tipo Requisito">
                                    <?= htmlspecialchars($mapaTipoReq[$requisito['tipo_req']] ?? $requisito['tipo_req']) ?>
                                </td>
                                <td data-label="Tópico de Requisito"><?= htmlspecialchars($requisito['requisito_topico']) ?>
                                </td>
                                <td data-label="Status"><?= htmlspecialchars($requisito['requisitos_status']) ?></td>
                                <td class="action-buttons" data-label="Ações">
                                    <a href="toggle_status.php?id=<?= $requisito['idrequisitos'] ?>" class="btn-inativar">
                                        <?php
                                        $status = strtolower($requisito['requisitos_status']);
                                        if ($status === 'inativo') {
                                            echo '<i class="fa fa-check"></i> Ativar';
                                        } else {
                                            echo '<i class="fa fa-times"></i> Inativar';
                                        }
                                        ?>
                                    </a>

                                    <a href="editar.php?id=<?= $requisito['idrequisitos'] ?>" class="btn-editar">
                                        <i class="fas fa-pencil-alt"></i> Editar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Nenhum requisito encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div id="toast" class="toast"></div>

        <div id="paginationControls" class="pagination-controls"></div>
    </div>

    <script>
        <?php if (isset($_SESSION['mensagem'])): ?>
            let mensagem = "<?= addslashes($_SESSION['mensagem']); ?>";
            let tipoMensagem = "<?= $_SESSION['tipo_mensagem']; ?>";
            let toast = document.getElementById('toast');

            function showToast(message, type) {
                toast.innerHTML = message;
                toast.classList.add('show', type);
                setTimeout(() => {
                    toast.classList.remove('show', type);
                }, 3000);
            }

            showToast(mensagem, tipoMensagem);
            <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
        <?php endif; ?>

        const rowsPerPage = 8; // Defina quantas linhas deseja exibir por página
        let currentPage = 1;
        let rows = [];
        let filteredRows = [];

        function displayTable(page) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const rowsToDisplay = filteredRows.length > 0 ? filteredRows : rows;

            rows.forEach(row => row.style.display = "none");
            rowsToDisplay.forEach((row, index) => {
                if (index >= start && index < end) {
                    row.style.display = "";
                }
            });
        }

        function updatePagination() {
            const paginationControls = document.getElementById("paginationControls");
            paginationControls.innerHTML = '';

            const totalPages = Math.ceil((filteredRows.length > 0 ? filteredRows.length : rows.length) / rowsPerPage);
            if (totalPages <= 1) return;

            // Mostrar apenas 5 botões de página de cada vez
            const maxButtonsToShow = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxButtonsToShow / 2));
            let endPage = Math.min(totalPages, startPage + maxButtonsToShow - 1);

            // Ajustar o início se o número total de páginas for menor do que o máximo
            if (endPage - startPage < maxButtonsToShow - 1) {
                startPage = Math.max(1, endPage - maxButtonsToShow + 1);
            }

            // Criar botões de página
            for (let i = startPage; i <= endPage; i++) {
                const button = document.createElement('button');
                button.innerText = i;
                button.classList.add('pagination-button');
                button.addEventListener('click', () => {
                    currentPage = i;
                    displayTable(currentPage);
                    updatePagination();
                });

                if (i === currentPage) {
                    button.classList.add('active');
                }

                paginationControls.appendChild(button);
            }

            // Adicionar botão "Anterior" se não estiver na primeira página
            if (currentPage > 1) {
                prevButton.addEventListener('click', () => {
                    currentPage--;
                    displayTable(currentPage);
                    updatePagination();
                });
                paginationControls.prepend(prevButton);
            }

            // Adicionar botão "Próximo" se não estiver na última página
            if (currentPage < totalPages) {
                nextButton.addEventListener('click', () => {
                    currentPage++;
                    displayTable(currentPage);
                    updatePagination();
                });
                paginationControls.appendChild(nextButton);
            }
        }

        function removeAcentos(texto) {
            return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        }

        function filterTable() {
            const input = removeAcentos(document.getElementById('searchInput').value.toLowerCase());
            const status = document.getElementById('statusFilter').value;

            filteredRows = rows.filter(row => {
                const topic = removeAcentos(row.cells[1].innerText.toLowerCase());
                const rowStatus = row.cells[2].innerText;

                const matchesTopic = topic.includes(input);
                const matchesStatus = status === "" || rowStatus === status;

                return matchesTopic && matchesStatus;
            });

            currentPage = 1;

            const tbody = document.querySelector("#requisitoTable tbody");
            tbody.innerHTML = '';

            if (filteredRows.length === 0) {
                const newRow = tbody.insertRow();
                const cell = newRow.insertCell();
                cell.colSpan = 5;
                cell.textContent = "Nenhum requisito encontrado.";
            } else {
                filteredRows.forEach(row => tbody.appendChild(row));
            }

            displayTable(currentPage);
            updatePagination();
        }

        function initPagination() {
            rows = Array.from(document.querySelectorAll("#requisitoTable tbody tr")); // busca todas as linhas
            filteredRows = [...rows];  // usar spread operator.
            const tbody = document.querySelector("#requisitoTable tbody")
            filteredRows.forEach((element, index) => {
                rows[index].remove()
                tbody.appendChild(element)
            });
            displayTable(currentPage);
            updatePagination();
        }
        window.onload = initPagination
    </script>
</body>

</html>