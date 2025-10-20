<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');

include __DIR__ . '/../sidebar.php';
include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador', 'Professor']);
include '../conexao.php';

// Configurações de paginação
$registros_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Função para exibir a tabela com registros e paginação
function exibirTabela($pdo, $query = '', $pagina_atual = 1, $registros_por_pagina = 10)
{
    $offset = ($pagina_atual - 1) * $registros_por_pagina;

    // Consulta para obter registros filtrados
    $sql = "SELECT * FROM tipomaquina WHERE tipomaquina_nome LIKE :query LIMIT $registros_por_pagina OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':query' => "%$query%"]);

    // Consulta para contar o total de registros filtrados
    $sql_total = "SELECT COUNT(*) FROM tipomaquina WHERE tipomaquina_nome LIKE :query";
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute([':query' => "%$query%"]);
    $total_registros = $stmt_total->fetchColumn();
    $total_paginas = ceil($total_registros / $registros_por_pagina);

    if ($stmt->rowCount() > 0) {
        echo "<table class='tabela'>";
        echo "<tr>
        <thead>
                <th>Nome</th>
                <th>Status</th>
                <th>Ações</th>
                </thead>
            </tr>";

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td data-label='Nome'>" . htmlspecialchars($row['tipomaquina_nome']) . "</td>";
            echo "<td data-label='Status' id='status-{$row['idtipomaquina']}'>" . htmlspecialchars($row['tipomaquina_status']) . "</td>";

            echo "<td>
                    <div class='botoes-acoes'>
                        <a href='editar.php?id=" . htmlspecialchars($row['idtipomaquina']) . "' class='botao-acao'>
                            <i class='fas fa-pencil-alt'></i> Editar
                        </a>
                        <button onclick='toggleStatus({$row['idtipomaquina']})' id='btn-{$row['idtipomaquina']}' class='botao-acao'>
                            <i class='fa " . ($row['tipomaquina_status'] == 'Ativo' ? 'fa-times' : 'fa-check') . "'></i> " .
                ($row['tipomaquina_status'] == 'Ativo' ? 'Desativar' : 'Ativar') . "
                        </button>
                        <a href='excluir.php?id=" . htmlspecialchars($row['idtipomaquina']) . "' class='botao-acao'>
                            <i class='fas fa-trash-alt'></i> Excluir
                        </a>
                    </div>
                  </td>";
            echo "</tr>";
        }
        echo "</table>";

        // Paginação
        echo "<div class='paginacao'>";
        for ($i = 1; $i <= $total_paginas; $i++) {
            echo "<a href='javascript:void(0)' onclick='loadTable($i)'" . ($i == $pagina_atual ? " class='active'" : "") . ">$i</a>";
        }
        echo "</div>";
    } else {
        echo "<p>Nenhum resultado encontrado.</p>";
    }
}

// Se for uma requisição AJAX, exibe apenas a tabela
if (isset($_GET['ajax'])) {
    exibirTabela($pdo, $query, $pagina_atual, $registros_por_pagina);
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.8em;
        }

        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: auto;
            
        }

        .botao-acao {
            background-color: #B22222;
            color: #fff;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            border: 1px solid black;
        }

        .botao-acao:hover {
            background-color: #8B0000;
        }

        .botoes-acoes {
            display: flex;
            gap: 10px;
        }

        .search-bar {
            padding: 8px;
            width: 20%;
            border-radius: 4px;
            border: 1px solid #ddd;

        }

        .tabela tr {
            border: 1px solid #ddd;
        }

        .tabela {
            width: 100%;
            border-collapse: collapse;
        }
        .paginacao {
            margin-top: 20px;
            text-align: center;
        }

        .paginacao a {
            margin: 0 5px;
            cursor: pointer;
            text-decoration: none;
            background-color: #fff;
            color: black;
            padding: 5px;
            border-radius: 3px;
        }

        .paginacao a:hover {
            background-color: gainsboro;
        }

        .paginacao a.active {
            color: white;
            background-color: dimgray;
        }
        @media (max-width: 650px) {

   select{
    width: 50%;
   }
    input[type="text"]{
        width: 65% ;
    }
.TabelaMotor td {
    display: flex;
}
body{
    margin-top: 55px;
}

.TabelaMotor td::before {
    content: attr(data-label);
    font-weight: bold;
    color: #555;
    text-transform: uppercase;
    margin-right: 5px;
}
thead, tbody:first-of-type {
    display: none;
}
thead{
    display: none;
}


table,
tbody,
th{
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
    display: flex;
    flex-direction: column;
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
    width: 71px;
}

.reset-password-btn a {
    width: 120px;
}
}
    </style>
</head>

<body>
        <h1>Tipos de Máquina</h1>
        <!-- Barra de pesquisa -->
        <input type="text" id="search" class="search-bar" placeholder="Pesquisar..." onkeyup="loadTable()">
    <!-- Contêiner da tabela -->
    <div id="table-container">
        <?php exibirTabela($pdo); ?>
    </div>

    <script>
        function loadTable(page = 1) {
            const query = document.getElementById('search').value;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `?ajax=1&query=${query}&pagina=${page}`, true);
            xhr.onload = function () {
                if (this.status === 200) {
                    document.getElementById('table-container').innerHTML = this.responseText;
                }
            };
            xhr.send();
        }

        function toggleStatus(id) {
            const button = document.getElementById(`btn-${id}`);
            const statusElement = document.getElementById(`status-${id}`);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'toggle_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (this.status === 200) {
                    const response = JSON.parse(this.responseText);
                    if (response.success) {
                        statusElement.textContent = response.new_status;
                        button.innerHTML = response.new_status === 'Ativo'
                            ? `<i class='fa fa-times'></i> Desativar`
                            : `<i class='fa fa-check'></i> Ativar`;
                    }
                }
            };
            xhr.send(`id=${id}`);
        }
    </script>

</body>

</html>