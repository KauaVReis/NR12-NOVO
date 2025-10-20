<?php
// Obtém o diretório base do servidor
include './atualizar_status_maquinas.php';

$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');
include __DIR__ . '/../sidebar.php';

// Incluindo a conexão com o banco de dados
include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador', 'Professor', 'Manutencao']);

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Falha na conexão: " . $e->getMessage());
}

// Configurações de paginação
$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Captura os filtros de pesquisa (NI ou Nome e Status)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Consulta para contar total de registros com filtro
$total_sql = "SELECT COUNT(*) FROM maquina m
              JOIN tipomaquina tm ON m.tipomaquina_id = tm.idtipomaquina
              JOIN setor s ON m.setor_id = s.idsetor
              WHERE 1=1"; // Filtro padrão para WHERE dinâmico

if (!empty($search)) {
    $total_sql .= " AND (m.maquina_ni LIKE :search 
                     OR m.maquina_fabricante LIKE :search
                     OR tm.tipomaquina_nome LIKE :search)";
}

if (!empty($status_filter)) {
    $total_sql .= " AND m.maquina_status = :status";
}

$total_stmt = $pdo->prepare($total_sql);

if (!empty($search)) {
    $total_stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
if (!empty($status_filter)) {
    $total_stmt->bindValue(':status', $status_filter, PDO::PARAM_STR);
}

$total_stmt->execute();
$total_records = $total_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Consulta para listar máquinas com filtro de NI ou Nome, Status e paginação
$sql = "SELECT m.idmaquina, m.maquina_ni, tm.tipomaquina_nome, s.setor_nome, 
               m.maquina_status, m.maquina_peso, m.maquina_fabricante, 
               m.maquina_modelo, m.maquina_ano, m.maquina_capacidade
        FROM maquina m 
        JOIN tipomaquina tm ON m.tipomaquina_id = tm.idtipomaquina
        JOIN setor s ON m.setor_id = s.idsetor
        WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (m.maquina_ni LIKE :search 
                 OR m.maquina_fabricante LIKE :search
                 OR tm.tipomaquina_nome LIKE :search)";
}

if (!empty($status_filter)) {
    $sql .= " AND m.maquina_status = :status";
}

$sql .= " LIMIT :limit OFFSET :offset"; // Adiciona a paginação

$stmt = $pdo->prepare($sql);

if (!empty($search)) {
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
if (!empty($status_filter)) {
    $stmt->bindValue(':status', $status_filter, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Máquinas</title>

    <script>
        function autoSubmitSearch() {
            // Submeter o formulário automaticamente sem atrasos
            document.getElementById("search-form").submit();
        }
    </script>

</head>

<body>
    <div class="container">
        <div class="input_ativo">
        <div id="results">
        <h1>Consulta de Máquinas</h1>

       <form class="search-form" id="search-form">
            <input type="text" name="search" id="search" 
                   value="<?= htmlspecialchars($search) ?>" 
                   placeholder="Digite o NI, Nome do Fabricante ou Tipo de Máquina" 
                   oninput="performSearch()" autofocus>
            <select name="status" id="status" onchange="performSearch()">
                <option value="">Todos</option>
                <option value="Ativo" <?= $status_filter === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="Inativo" <?= $status_filter === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </form>

            <?php include 'listar_maquinas.php'; ?>
        </div>


    </div>
</body>
<script>
let timeout = null; // Variável para armazenar o timeout

function performSearch() {
    // Cancela o timeout anterior, caso o usuário digite rápido
    clearTimeout(timeout);

    // Define um novo timeout para realizar a busca após 500ms (meio segundo)
    timeout = setTimeout(function() {
        // Cria um objeto XMLHttpRequest para realizar a requisição AJAX
        const xhr = new XMLHttpRequest();
        const searchValue = document.getElementById('search').value;
        const statusValue = document.getElementById('status').value;

        // Configura a requisição AJAX
        xhr.open('GET', 'seu_arquivo_de_busca.php?search=' + encodeURIComponent(searchValue) + '&status=' + encodeURIComponent(statusValue), true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        // Envia a requisição
        xhr.send();

        // Quando a requisição for concluída, atualiza a página sem recarregar
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('results').innerHTML = xhr.responseText;
            }
        };
    }, 500); // 500ms de atraso
}

// Adiciona eventos para os campos de busca e status
document.getElementById('search').addEventListener('input', performSearch);
document.getElementById('status').addEventListener('change', performSearch);

</script>
<style>
    form{
        margin: auto;
        flex-direction: row;
        gap: 25px;
    }
   
    body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: auto;
    }

    .pagination {
        margin: 1 5px;
        padding: 3px 5px;
        cursor: pointer;
        border: none;
    }

    .pagination a {
        margin: 0 6px;
        padding: 5px 8px;
        cursor: pointer;
        text-decoration: none;
        color: black;
        background-color: #fff;
        font-size: 15px;
        font-weight: bold;

    }

    .pagination a:hover {
        background-color: #c2c2c2;

    }

    .pagination a.active {
        background-color: dimgray;
        color: white;
    }

    /* Estilos para computadores */
    .pagination-controls {
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

    .container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        max-width: 345px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        max-width: 1600px;
    }

    th,
    td {
        text-align: center;
        font-size: 14px;
        white-space: nowrap;
    }
   select{
    padding: 9px;
    width: 20%;
    background-color: #fff;
    border-color: #000;
    color: #000;
   }
   .input_ativo{
    display: flex;
    justify-content: center;
    text-align: center;
    flex-direction: column;
  
   }
    .action-buttons a {
        text-decoration: none;
        padding: 6px 10px;
        border-radius: 4px;
        color: white;
        font-weight: bold;
        margin: 0 2px;
        transition: background-color 0.3s;
        align-items: center;
        gap: 5px;
        background-color: #a80813;
        width: 120px;

    }

    .pagination-controls {
        text-align: center;
        font-weight: bold;
    }


    .container_consultar_req h1 {
        text-align: center;
        margin-bottom: 10px;
    }

    .btn-inativar,
    .btn-editar,
    .btn-excluir {
        background-color: #B22222;
        border: 1px solid black;
        color: white;
        font-weight: bold;
        padding: 8px 12px;
        border-radius: 4px;
        margin: 0 5px;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    #itemsPerPage {
        width: 8%;
        margin-left: 75px;
        border: 2px solid #8B0000;
    }

    .btn-inativar:hover,
    .btn-editar:hover {
        background-color: #c82333;
    }

    .btn-excluir {
        background-color: #8B0000;
    }

    .btn-excluir:hover {
        background-color: #A52A2A;
    }

    /* Responsividade para telas menores */
    @media (max-width: 1024px) {

h1 {
    font-size: 1.8em;
    margin-top: 10px;
}

th,
td {
    padding: 5px;
    text-align: center;
    font-size: 13px;
    white-space: normal;
}

/* Botões de ação em tablets */
.action-buttons {
    display: grid;
    flex-direction: column;
    gap: 6px;
    justify-content: center;
    align-items: center;
}

.action-buttons a {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
    padding: 8px 12px;
    font-size: 12px;
}

}

/* Responsividade para dispositivos móveis */
@media (max-width: 480px) {
    .container{
        margin-top: 70px;
    }
   select{
    width: 40%;
    background-color: #fff;
    border-color: #000;
   }
    input[type="text"]{
        width: 80% !important;
    }

.cabecalho {
    display: none;
}

.TabelaMotor td {
    display: flex;
}

.TabelaMotor td::before {
    content: attr(data-label);
    font-weight: bold;
    color: #555;
    text-transform: uppercase;
    margin-right: 5px;
}

table,
thead,
tbody,
th,
td {
    display: block;
    padding: 5px 8px;
}

tbody {
    background-color: #e0e0e0;
    border-radius: 5px;
}

table {
    width: 100%;
height: 100%;
}

th,
td {
    font-size: 0.8em;
    padding: 5px;
}

tr {
    display: block;
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
td::before {
        content: attr(data-label); /* Exibe o cabeçalho correspondente */
        font-weight: bold;
        color: #555;
        text-transform: uppercase;
        margin-right: 10px;
        flex-shrink: 0;
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
    align-items: stretch;
}

.search-form {
    justify-content: center;
    align-items: center;
}

/* Alinha o label e o select do filtro de turma na mesma linha */
.action-buttons {
    display: flex;
    flex-direction: row;
    justify-content: space-between;

}

.action-buttons a {
    width: 82px;
}

.reset-password-btn a {
    width: 120px;
}
}



</style>


</html>