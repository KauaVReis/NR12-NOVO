<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
define('BASE_URL', '../../nr12/');

include $_SERVER['DOCUMENT_ROOT'] . '/nr12/sidebar.php';
include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador', 'Professor']);

// Captura o termo de busca e o filtro de status
$termo_busca = isset($_GET['query']) ? $_GET['query'] : '';
$status_filtro = isset($_GET['status']) ? $_GET['status'] : '';

// Configuração de paginação
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Consulta para contar total de registros com filtro
$total_sql = "SELECT COUNT(*) FROM motor WHERE (motor_fabricante LIKE :busca OR motor_modelo LIKE :busca OR motor_status LIKE :busca)";

if ($status_filtro) {
    $total_sql .= " AND motor_status = :status";
}

$total_stmt = $pdo->prepare($total_sql);
$total_stmt->bindValue(':busca', "%$termo_busca%", PDO::PARAM_STR);
if ($status_filtro) {
    $total_stmt->bindValue(':status', $status_filtro, PDO::PARAM_STR);
}
$total_stmt->execute();
$total_records = $total_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para obter registros filtrados pela busca e status com paginação
    $sql = "
            SELECT m.idmotor, m.motor_fabricante, m.motor_modelo, m.motor_status, m.motor_potencia, m.motor_tensão, m.motor_corrente
            FROM motor m
            WHERE (m.motor_fabricante LIKE :busca OR m.motor_modelo LIKE :busca OR m.motor_status LIKE :busca)
        ";

    if ($status_filtro) {
        $sql .= " AND m.motor_status = :status";
    }

    $sql .= " LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':busca', "%$termo_busca%", PDO::PARAM_STR);
    if ($status_filtro) {
        $stmt->bindValue(':status', $status_filtro, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
    $stmt->execute();

    echo "<h1> Consultar Motores</h1>";

    // Barra de pesquisa com filtro de status
    echo "<form class='search-form'>
                <div class='search-bar consultafuncionario'>
                    <input type='text' id='searchInput' class='search-input consultafuncionario' placeholder='Pesquisar por Fabricante, Modelo ou Status' value='" . htmlspecialchars($termo_busca) . "' onkeyup='searchMotors()'>
                </div>
                <div class='status-filter'>
                    <select name='status' id='statusFilter' onchange='this.form.submit()'>
                        <option value=''>Todos</option>
                        <option value='Ativo'" . ($status_filtro == 'Ativo' ? ' selected' : '') . ">Ativo</option>
                        <option value='Inativo'" . ($status_filtro == 'Inativo' ? ' selected' : '') . ">Inativo</option>
                    </select>
                </div>
            </form>";

    // Exibição dos resultados
// Exibição dos resultados
if ($stmt->rowCount() > 0) {
    echo "<div class='table-container'>"; // Adicionei esta linha
    echo "<table class='TabelaMotor'>";
    echo "<tr class='cabecalho'>
                <th>Fabricante</th>
                <th>Modelo</th>
                <th>Potência</th>
                <th>Tensão</th>
                <th>Corrente</th>
                <th>Status</th>
                <th colspan='3'>Ações</th>
            </tr>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                    <td data-label='Fabricante'>" . htmlspecialchars($row['motor_fabricante']) . "</td>
                    <td data-label='Modelo'>" . htmlspecialchars($row['motor_modelo']) . "</td>
                    <td data-label='Potência'>" . htmlspecialchars($row['motor_potencia']) . "</td>
                    <td data-label='Tensão'>" . htmlspecialchars($row['motor_tensão']) . "</td>
                    <td data-label='Corrente'>" . htmlspecialchars($row['motor_corrente']) . "</td>
                    <td data-label='Status'>" . htmlspecialchars($row['motor_status']) . "</td>";

        // Determina a ação de ativar/desativar
        $toggleAction = ($row['motor_status'] === 'Ativo') ? 'desativar' : 'ativar';
        $toggleText = ($row['motor_status'] === 'Ativo') ? 'Desativar' : 'Ativar';
        $toggleIcon = ($toggleAction === 'desativar') ? '<i class="fa fa-times"></i>' : '<i class="fa fa-check"></i>';
        $toggleClass = ($row['motor_status'] === 'Ativo') ? 'Botao' : 'Botao';

        echo "<td data-label='Ações'>
                    <div class='action-buttons'>
                        <a class='$toggleClass' href='toggle_motor_status.php?id=" . htmlspecialchars($row['idmotor']) . "'>
                            $toggleIcon $toggleText
                        </a>
                        <a href='editar.php?id=" . htmlspecialchars($row['idmotor']) . "' class='Botao'><i class='fas fa-pencil-alt'></i> Editar</a>
                        <a href='excluir.php?id=" . htmlspecialchars($row['idmotor']) . "' class='Botao'><i class='fas fa-trash-alt'></i> Excluir</a>
                    </div>
                </td>
                </tr>";
    }

    echo "</table>";
    echo "</div>"; // Fechei a div

    }
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>

<!-- Paginação -->
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&query=<?= urlencode($termo_busca) ?>&status=<?= $status_filtro ?>">« Anterior</a>
    <?php endif; ?>

    <?php
    $start = max(1, $page - 2);
    $end = min($total_pages, $page + 2);

    if ($start > 1) {
        echo '<a href="?page=1&query=' . urlencode($termo_busca) . '&status=' . $status_filtro . '">1</a>';
        if ($start > 2) {
            echo '<span>...</span>';
        }
    }

    for ($i = $start; $i <= $end; $i++): ?>
        <a href="?page=<?= $i ?>&query=<?= urlencode($termo_busca) ?>&status=<?= $status_filtro ?>"
            class="<?= ($i === $page) ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            echo '<span>...</span>';
        }
        echo '<a href="?page=' . $total_pages . '&query=' . urlencode($termo_busca) . '&status=' . $status_filtro . '">' . $total_pages . '</a>';
    }
    ?>

    <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?>&query=<?= urlencode($termo_busca) ?>&status=<?= $status_filtro ?>">Próximo »</a>
    <?php endif; ?>
</div>

<script>
    document.getElementById('searchInput').addEventListener('input', function () {
        const query = this.value;
        const status = document.getElementById('statusFilter').value;
        fetch(`?query=${encodeURIComponent(query)}&status=${encodeURIComponent(status)}`)
            .then(response => response.text())
            .then(html => {
                document.querySelector('.TabelaMotor').innerHTML = new DOMParser()
                    .parseFromString(html, 'text/html')
                    .querySelector('.TabelaMotor').innerHTML;
            })
            .catch(error => console.error('Erro na pesquisa:', error));
    });
</script>
<style>
    .search-form {
        display: flex;
        gap: 20px;
    }


    /* Estilos para o título */
    h1 {
        text-align: center;

    }

    /* Estilos para a tabela */
    table {
        border-collapse: collapse;

        /* Define uma largura mais confortável para a tabela */
        border: none;
        /* Remove a borda externa */
        text-align: center;
        /* Centraliza o texto dentro das células */
        height: auto;
        margin: 0;
    }

    th,
    td {
        text-align: center;
        font-size: 14px;
        white-space: nowrap;
    }

    th {
        padding: 10px;
    }

    /* Estilos para a paginação */
    .pagination {
        text-align: center;
        /* Centraliza a paginação */
    }

    .pagination a {
        margin: 0 5px;
        padding: 5px 10px;
        cursor: pointer;
        border: none;
        text-decoration: none;
        color: #000000;
        background-color: #fff;
    }

    .pagination a:hover {
        font-weight: bold;
        background-color: #A9A9A9;
    }

    .pagination a.active {
        background-color: dimgray;
        color: white;
    }


    #statusFilter {
        padding: 7px;
        border: 2px solid #000000;
        border-radius: 4px;
        background-color: #f8f8f8;
        color: #000000;
        cursor: pointer;
        font-weight: bold;
        width: 140%;
    }

    html,
    body {
        height: 100%;
        /* Garante que a altura da página ocupe toda a altura da tela */
        margin: 0;
        /* Remove margens padrão */
    }

    form {
        flex-direction: row;
        width: auto;
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
    }
  
    @media (max-width: 1024px) {

        h1 {
            font-size: 1.8em;
            margin-top: 10px;
        }

        th,
        td {
            padding: 10px;
            text-align: center;
            font-size: 13px;
        }

        /* Botões de ação em tablets */
        .action-buttons {
            display: grid;
            flex-direction: column;
            gap: 8px;
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
        @media (max-width: 550px) {
            body{
                margin-top: 40px;
                /* Remove margens padrão */
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
            td {
                display: block;
                padding: 5px 8px;
            }

            tbody {
                background-color: #e0e0e0;
                border-radius: 5px;
            }

            table {
                width: 90%;
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

            thead {
                display: none;
            }

            form {
                margin: auto;
                display: flex;
                flex-direction: column;
                gap: 1px;
                align-items: flex-start;
                width: 95%;
                /* Ajusta o formulário para ocupar toda a largura */
                background-color: #cfcfcf;
            }
            #searchInput{
                width: 285px;
            }

            .search-form {
                align-items: center;
            }

            /* Alinha o label e o select do filtro de turma na mesma linha */
            .action-buttons {
                display: flex;
                flex-direction: row;
                gap: 1px;
            }

            .action-buttons a {
                border: 1px solid black;    
                width: 71px;
            }

            .reset-password-btn a {
                width: 120px;
            }
        }
</style>