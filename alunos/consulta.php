<?php
ob_start();
session_start();

$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');

include __DIR__ . '/../sidebar.php';

include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador', 'Professor']);

// Defina um valor fixo para o número de itens por página
$itens_por_pagina = 10;

$pagina_atual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

$termo_busca = isset($_GET['query']) ? $_GET['query'] : '';
$turma_filtro = isset($_GET['turma']) ? $_GET['turma'] : '';
$status_filtro = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

try {
    // Obter o total de alunos com o filtro de busca e status
    $sql_total = "SELECT COUNT(*) as total FROM aluno 
                  JOIN turmas t ON aluno.turmas_id = t.idturmas
                  WHERE (aluno_nome LIKE :busca OR aluno_matricula LIKE :busca 
                         OR turma_nome LIKE :busca OR aluno_status LIKE :busca)
                  AND (t.idturmas LIKE :turma OR :turma = '')
                  AND (aluno_status LIKE :status OR :status = '')";
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->bindValue(':busca', "%$termo_busca%");
    $stmt_total->bindValue(':turma', "%$turma_filtro%");
    $stmt_total->bindValue(':status', $status_filtro);
    $stmt_total->execute();
    $total_alunos = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];

    // Buscar os alunos com base na busca, filtro de turma e status
    $sql = "SELECT a.idaluno, a.aluno_nome, a.aluno_matricula, t.turma_nome, a.aluno_status
            FROM aluno a
            JOIN turmas t ON a.turmas_id = t.idturmas
            WHERE (a.aluno_nome LIKE :busca OR a.aluno_matricula LIKE :busca 
                   OR t.turma_nome LIKE :busca OR a.aluno_status LIKE :busca)
            AND (t.idturmas LIKE :turma OR :turma = '')
            AND (aluno_status LIKE :status OR :status = '')
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':busca', "%$termo_busca%");
    $stmt->bindValue(':turma', "%$turma_filtro%");
    $stmt->bindValue(':status', $status_filtro);
    $stmt->bindParam(':limit', $itens_por_pagina, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obter todas as turmas para o filtro
    $stmt_turmas = $pdo->query("SELECT idturmas, turma_nome FROM turmas");
    $turmas = $stmt_turmas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao buscar alunos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Alunos</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<style>
    label {
        display: flex;
    }

    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
    }

    #filtroFormAluno {
        box-shadow: none;
        border: none;
        background-color: #cfcfcf;
        margin: 0;
        transition: none;

    }

    .filTurma {
        margin-left: 2px;
    }

    .filtroaluno {
        margin-right: 700px !important;
    }


    #turma {
        background-color: #fff;
        border: none;
        width: 120%;
    }


    .search-bar {
        display: flex;
        justify-content: center;

        /* Margem superior e inferior */
    }

    .search-input {
        padding: 10px;
        width: 100%;
        /* Ocupa 100% da largura disponível */
        max-width: 400px;
        /* Largura máxima para dispositivos maiores */
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
    }


    table {
        width: 100%;
        margin-top: 5px !important;
        /* A tabela ocupa 100% da largura disponível */
        border: none;
    }
    #itensPorPagina {
        width: 80px;
        display: flex;

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

    .btn-inativar:hover,
    .btn-editar:hover,
    .btn-excluir:hover {
        background-color: #c82333;
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
    .pagination a.active {
    background-color: dimgray;
    color: white; /* Cor do texto */
}
    


    @media (max-width: 480px) {
        .pagination {
            margin: 10px;
            padding-bottom: 20px;
        }

        .pagination a {
            font-size: 12px;
            /* Ajuste o tamanho da fonte */
            padding: 6px 10px;
            /* Ajuste o tamanho do botão */
            margin: 0 2px;
            /* Menos margem entre os botões */
        }
    }

    /* Responsividade para tablets */
    /* Responsividade para tablets */
    @media (max-width: 940px) {
        #itensPorPagina {
            margin-left: 390px;
        }

        .table-container {
            max-height: 650px;
            width: 100%;

        }

        table {
            font-size: 0.85em;
            /* Reduz o tamanho da fonte */
        }

        th,
        td {
            padding: 8px 3px;
           
            /* Diminui o padding para economizar espaço */
        }

        .search-input {
            width: 100%;
            /* A barra de pesquisa ocupa 100% da largura */
            font-size: 15px;
            /* Tamanho de fonte menor */
            border: none;
        }

        .action-buttons a {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

    }
    /* Responsividade para dispositivos móveis */
    @media (max-width: 480px) {
        .table-container {
            padding: 0 5px;

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
            width: 100%;
            /* Ajusta o formulário para ocupar toda a largura */
            background-color: #cfcfcf;
        }

        /* Alinha o filtro de aluno e de turma na mesma linha */
        .search-bar {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        /* Alinha o label e o select do filtro de turma na mesma linha */

        #searchInput {
            width: 100%;
            box-sizing: border-box;
        }

        #turma {
            width: 100%;
            max-width: 350px;
        }
    }
    th{
        padding: 7px;
    }

    #status {
        width: 120px;
        height: fit-content;
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
    }

    .filter-row {
        display: flex;
        gap: 80px;
        align-items: center;
        margin-top: 10px;
    }
</style>

<body>
    <?php if (isset($_SESSION['mensagem']) && isset($_SESSION['tipo_mensagem'])): ?>
        <div id="toast" class="toast <?= htmlspecialchars($_SESSION['tipo_mensagem']) ?>">
            <?= htmlspecialchars($_SESSION['mensagem']); ?>
        </div>
        <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
    <?php endif; ?>

    <div class="caixa">
        <h1 style="margin-top: 80px; display: flex; justify-content: center;">Alunos Cadastrados</h1>

        <div class="search-bar">
            <form method="get" id="filtroFormAluno">
                <input type="text" id="searchInput" name="query" class="search-input"
                    placeholder="Pesquisar por Nome, Matrícula ou Status" onkeyup="filterTable()">
                <!-- Coloca os nomes acima dos selects -->
                <div class="filter-row">
                    <div>
                        <select name="turma" id="turma" onchange="this.form.submit()">
                            <option value="">Todas as Turmas</option>
                            <?php foreach ($turmas as $turma): ?>
                                <option value="<?= $turma['idturmas'] ?>" <?= $turma_filtro == $turma['idturmas'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($turma['turma_nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <select name="status" id="status" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="Ativo" <?= $status_filtro === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="Inativo" <?= $status_filtro === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>


        <div class="table-container">
        <table id="alunosTable">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Matrícula</th>
                        <th>Turma</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($alunos) > 0): ?>
                        <?php foreach ($alunos as $aluno): ?>
                            <tr>
                                <td data-label="Nome"><?= htmlspecialchars($aluno['aluno_nome']) ?></td>
                                <td data-label="Matrícula"><?= htmlspecialchars($aluno['aluno_matricula']) ?></td>
                                <td data-label="Turma"><?= htmlspecialchars($aluno['turma_nome']) ?></td>
                                <td data-label="Status"><?= htmlspecialchars($aluno['aluno_status']) ?></td>
                                <td data-label="Ações" class="action-buttons">
                                    <a href="toggle_status.php?id=<?= $aluno['idaluno'] ?>" class="btn-inativar">
                                        <?php if ($aluno['aluno_status'] === 'Ativo') {
                                            echo '<i class="fa fa-times"></i> Desativar';
                                        } else {
                                            echo '<i class="fa fa-check"></i> Ativar';
                                        } ?>
                                    </a>
                                    <a href="alterar.php?id=<?= $aluno['idaluno'] ?>" class="btn-editar">
                                        <i class="fas fa-pencil-alt"></i> Editar
                                    </a>
                                    <a href="excluir.php?id=<?= $aluno['idaluno'] ?>" class="btn-excluir">
                                        <i class="fas fa-trash-alt"></i> Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">Nenhum dado encontrado</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        

        <div class="pagination">
            <?php
            $total_paginas = ceil($total_alunos / $itens_por_pagina); // Total de páginas
            $num_links = 5; // Número de links a mostrar por vez
            $range = 2; // Quantas páginas antes e depois da página atual exibir
            
            // Determinar o início e o fim da página
            $inicio = max(1, $pagina_atual - $range); // Evitar números negativos
            $fim = min($total_paginas, $pagina_atual + $range); // Limitar ao total de páginas
            
            // Se o número total de páginas for maior que o número de links que queremos exibir
            if ($total_paginas > $num_links) {

                // Exibir os números das páginas
                for ($i = $inicio; $i <= $fim; $i++) {
                    echo '<a href="?pagina=' . $i . '&query=' . urlencode($termo_busca) . '&turma=' . $turma_filtro . '&status=' . $status_filtro . '" class="' . ($i == $pagina_atual ? 'active' : '') . '">' . $i . '</a>';
                }

                // Exibir link "próximo"
            } else {
                // Exibir todos os números de páginas se o total de páginas for menor ou igual ao número de links que queremos mostrar
                for ($i = 1; $i <= $total_paginas; $i++) {
                    echo '<a href="?pagina=' . $i . '&query=' . urlencode($termo_busca) . '&turma=' . $turma_filtro . '&status=' . $status_filtro . '" class="' . ($i == $pagina_atual ? 'active' : '') . '">' . $i . '</a>';
                }
            }
            ?>
        </div>
        </div>
        <script>
       function filterTable() {
            const query = document.getElementById("searchInput").value;
            const turma = document.getElementById("turma").value;
            const status = document.getElementById("status").value;

            const xhr = new XMLHttpRequest();
            xhr.open("GET", `index.php?query=${query}&turma=${turma}&status=${status}`, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.querySelector("#alunosTable tbody").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        </script>
</body>

</html>