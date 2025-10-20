<?php
// Configuração do diretório base
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');


include '../conexao.php';
include '../sidebar.php';

require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador']);

// Defina o status como "Todos" por padrão ou obtenha do parâmetro GET
$statusSelecionado = isset($_GET['status']) ? $_GET['status'] : 'Todos';

// Configurações de paginação
$registrosPorPagina = 10; // Quantidade de registros por página
$paginaAtual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $registrosPorPagina;

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Construa a consulta para contar os registros totais com base no status selecionado
    if ($statusSelecionado === 'Todos') {
        $sqlContagem = "SELECT COUNT(*) as total FROM curso";
        $stmtContagem = $pdo->prepare($sqlContagem);
    } else {
        $sqlContagem = "SELECT COUNT(*) as total FROM curso WHERE curso_status = :status";
        $stmtContagem = $pdo->prepare($sqlContagem);
        $stmtContagem->bindParam(':status', $statusSelecionado);
    }
    $stmtContagem->execute();
    $totalRegistros = $stmtContagem->fetch(PDO::FETCH_ASSOC)['total'];

    // Construa a consulta principal com limite e offset
    if ($statusSelecionado === 'Todos') {
        $sql = "SELECT idcurso, curso_nome, curso_status FROM curso LIMIT :offset, :limite";
        $stmt = $pdo->prepare($sql);
    } else {
        $sql = "SELECT idcurso, curso_nome, curso_status FROM curso WHERE curso_status = :status LIMIT :offset, :limite";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':status', $statusSelecionado);
    }
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limite', $registrosPorPagina, PDO::PARAM_INT);

    $stmt->execute();
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular o total de páginas
    $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Cursos</title>
    <style>
        /* Outros estilos continuam iguais */

        #statusFiltro {
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
            text-align: center;
            width: 5% !important;
            height: 7% !important;
            margin-top: 400px !important;
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

        .header-filtro {
            display: flex;
            justify-content: center;
            align-items: center; 
            gap: 20px;
        }

        /* css para computador */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        #table-container {
            margin: 35px auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .search-input {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 250px;
            max-width: 100%;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;

        }

        th,
        td {
            padding: 14px;
            text-align: center;
            font-size: 14px;
            white-space: nowrap;
        }
        .action-buttons a {
            text-decoration: none;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            margin: 0 2px;
            transition: background-color 0.3s;
            display: inline-block;
        }

        .btn-inativar,
        .btn-editar,
        .btn-excluir {
            background-color: #B22222;
            border: 1px solid black;
            padding: 8px 12px;
            border-radius: 4px;
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

        .pagination-controls {
            text-align: center;
        }

        #navegacaoPaginacao button {
            padding: 6px 10px;
            margin: 4px;
            /* Espaçamento uniforme entre os botões */
            cursor: pointer;
            font-weight: bold;
            /* Tamanho da fonte dos botões */
            border: 1px solid #ccc;
            /* Bordas arredondadas */
            background-color: #f0f0f0;
            /* Cor de fundo dos botões */

        }

        .status-filter {
            display: flex;
            flex-direction: column;
            align-items: center;

        }

        .status-filter label {
            margin-bottom: 5px;
            /* Dá um pequeno espaçamento entre o label e o select */
            font-weight: bold;
        }


        #navegacaoPaginacao button:hover {
            background-color: #A9A9A9;
            /* Cor de fundo quando selecionado */

        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
        }

        /* Responsividade para telas menores */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            #itensPorPagina {
                width: 12%;
                margin-right: 420px;
                border: 2px solid #8B0000;
            }

            th {
                padding: 10px;

            }

            td {
                padding: 5px;
            }

            th,
            td {
                font-size: 12px;
                white-space: normal;
            }

            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .search-bar {
                justify-content: flex-end;
            }

            .search-input {
                width: 100%;
                max-width: 100%;
                padding: 8px;
            }

            .pagination-button {
                padding: 2px 8px;
            }

            h1 {
                font-size: 30px;
            }
        }

        @media (max-width: 480px) {
            .table-responsive {
                width: 100%;
                padding: 20px 35px;
            }
            .header-filtro{
                flex-direction: column;
            }

            .action-buttons {
        display: flex;
        flex-direction: row; /* Alinha os botões verticalmente */
        gap: 5px; /* Espaçamento entre os botões */
        justify-content: center; /* Centraliza os botões */
        align-items: center;
    }


            .action-buttons a {
                display: flex;
                gap: 9px;

            }

            .table-container {
                padding: 0 5px;
                /* Aumenta a altura da caixa no mobile */
                overflow-y: auto;
                /* Adiciona rolagem vertical */
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
                width: 100%;
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

            td::before {
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
                gap: 10px;
                align-items: stretch;
            }

            /* Alinha o label e o select do filtro de turma na mesma linha */

            #searchInput {
                width: 280px;
                box-sizing: border-box;
            }

            #turma {
                width: 100%;
                max-width: 350px;
            }
        }
        /* Estilo para a navegação da paginação */
#navegacaoPaginacao {
    margin-top: 20px;
    display: flex;
    justify-content: center;
}

/* Estilo da lista de páginas */
.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
}

/* Estilo dos itens da página */
.page-item {
    margin: 0 5px;
}

/* Estilo para links das páginas */
.page-link {
    display: block;
    padding: 10px 15px;
    text-decoration: none;
    color: #333; /* Cor do texto padrão */
    background-color: #fff; /* Fundo branco */
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* Hover nos links (cor vermelha) */
.page-link:hover {
    background-color: red; /* Cor de fundo vermelha no hover */
    color: #fff; /* Texto branco no hover */
}

/* Estilo para o item ativo */
.page-item.active .page-link {
    background-color: #fff; /* Fundo branco para o item ativo */
    color: #333; /* Cor do texto padrão */
    border-color: #ddd; /* Cor de borda */
}

/* Estilo do link de paginação desativado */
.page-item.disabled .page-link {
    background-color: #f8f9fa;
    color: #6c757d;
    border-color: #ddd;
}

/* Estilo para o botão de paginação quando não houverem mais páginas */
.page-item.disabled .page-link:hover {
    background-color: #f8f9fa;
    color: #6c757d;
}

    </style>
</head>

<body>
    <div class="container">
        <div id="table-container">
            <h1>Consulta de Cursos</h1>
            <div class="header-filtro">
                <div class="search-bar">
                    <input type="text" id="searchInput" class="search-input" placeholder="Pesquisar curso"
                        onkeyup="filtrarTabela()">
                </div>
                <!-- Filtro de status -->
                <div class="status-filter">
                    <select id="statusFiltro" onchange="atualizarFiltroStatus()">
                        <option value="Todos" <?= $statusSelecionado === 'Todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="Ativo" <?= $statusSelecionado === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                        <option value="Inativo" <?= $statusSelecionado === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nome do Curso</th>
                            <th>Status</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody id="corpoTabela">
                        <?php if (count($cursos) > 0): ?>
                            <?php foreach ($cursos as $curso): ?>
                                <tr data-status="<?= htmlspecialchars($curso['curso_status']) ?>">
                                    <td data-label="Curso"><?= htmlspecialchars($curso['curso_nome']) ?></td>
                                    <td data-label="Status"><?= htmlspecialchars($curso['curso_status']) ?></td>
                                    <td data-label="Ação">
                                        <div class="action-buttons">
                                            <a href="toggle_status.php?id=<?= $curso['idcurso'] ?>" class="btn-inativar">
                                                <?= $curso['curso_status'] === 'Ativo' ? '<i class="fa fa-times"></i> Desativar' : '<i class="fa fa-check"></i> Ativar' ?>
                                            </a>
                                            <a href="editar.php?id=<?= $curso['idcurso'] ?>" class="btn-editar">
                                                <i class="fas fa-pencil-alt"></i> Editar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <!-- Linha para a mensagem "Nenhum curso encontrado" -->
                        <tr id="mensagemNenhumCurso" style="display:none;">
                            <td colspan="3">Nenhum curso encontrado.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Navegação de paginação -->
            <div id="navegacaoPaginacao">
                <?php if ($totalPaginas > 1): ?>
                    <nav>
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li class="page-item <?= $i === $paginaAtual ? 'active' : '' ?>">
                                    <a class="page-link" href="?status=<?= $statusSelecionado ?>&pagina=<?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Função para remover acentos e fazer a comparação sem diferenciar maiúsculas de minúsculas
        function removerAcentos(str) {
            const mapaAcentos = {
                'á': 'a', 'à': 'a', 'ã': 'a', 'â': 'a', 'ä': 'a', 'é': 'e', 'è': 'e', 'ê': 'e', 'ë': 'e',
                'í': 'i', 'ì': 'i', 'î': 'i', 'ï': 'i', 'ó': 'o', 'ò': 'o', 'õ': 'o', 'ô': 'o', 'ö': 'o',
                'ú': 'u', 'ù': 'u', 'û': 'u', 'ü': 'u', 'ç': 'c', 'Á': 'A', 'À': 'A', 'Ã': 'A', 'Â': 'A',
                'Ä': 'A', 'É': 'E', 'È': 'E', 'Ê': 'E', 'Ë': 'E', 'Í': 'I', 'Ì': 'I', 'Î': 'I', 'Ï': 'I',
                'Ó': 'O', 'Ò': 'O', 'Õ': 'O', 'Ô': 'O', 'Ö': 'O', 'Ú': 'U', 'Ù': 'U', 'Û': 'U', 'Ü': 'U',
                'Ç': 'C'
            };
            return str.split('').map(char => mapaAcentos[char] || char).join('');
        }

        function filtrarTabela() {
            const input = document.getElementById('searchInput');
            const filter = removerAcentos(input.value.toLowerCase()); // Remover acentos e transformar em minúsculas
            const tabela = document.getElementById('corpoTabela');
            const linhas = tabela.getElementsByTagName('tr');
            let encontrouResultados = false;

            // Verifica se o campo de pesquisa está vazio
            if (filter === "") {
                // Exibe todas as linhas quando o filtro estiver vazio
                for (let i = 0; i < linhas.length; i++) {
                    linhas[i].style.display = ''; // Exibe todas as linhas
                }
                // Não exibe a mensagem de "Nenhum curso encontrado"
                return;
            } else {
                // Caso o campo não esteja vazio, aplica o filtro
                for (let i = 0; i < linhas.length; i++) {
                    const tds = linhas[i].getElementsByTagName('td');
                    let encontrado = false;

                    for (let j = 0; j < tds.length; j++) {
                        const textoCell = removerAcentos(tds[j].textContent.toLowerCase());
                        if (textoCell.includes(filter)) {
                            encontrado = true;
                            encontrouResultados = true;
                            break;
                        }
                    }

                    linhas[i].style.display = encontrado ? '' : 'none'; // Exibe ou oculta a linha com base no filtro
                }
            }

            // Se não encontrar nenhum resultado, exibe a mensagem de "Nenhum curso encontrado"
            const mensagem = document.getElementById('mensagemNenhumCurso');
            if (!encontrouResultados && filter !== "") {
                mensagem.style.display = 'table-row';  // Exibe a linha da mensagem
            } else {
                mensagem.style.display = 'none'; // Oculta a linha da mensagem
            }
        }
        // Função para atualizar o filtro de status e recarregar a página
        function atualizarFiltroStatus() {
            const status = document.getElementById('statusFiltro').value;
            window.location.href = `?status=${status}`;
        }
    </script>
</body>

</html>
