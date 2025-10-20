<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');
?>

<?php
require_once '../verifica_permissao.php';
include __DIR__ . '/../sidebar.php';
include '../conexao.php';

verificaPermissao(['Adm', 'Coordenador']);

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Paginação
    $registros_por_pagina = 5; // Defina o número de registros por página
    $pagina_atual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
    $offset = ($pagina_atual - 1) * $registros_por_pagina;

    // Consulta para obter o total de registros
    $sql_total = "SELECT COUNT(*) FROM unidade";
    $stmt_total = $pdo->query($sql_total);
    $total_registros = $stmt_total->fetchColumn();
    $total_paginas = ceil($total_registros / $registros_por_pagina);


    $sql = "SELECT * FROM unidade LIMIT :limite OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limite', $registros_por_pagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
    <title>Unidades Cadastradas</title>
    <style>
    @media (max-width: 480px) {

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

    tr {
        border-bottom: 1px solid #ddd;
        padding-bottom: 8px;
        margin-bottom: 8px;
        background-color: #f8f8f8;
        border-radius: 6px;
        box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.1);
    }

    thead {
        display: none;
    }

    td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px;
    }

    td:before {
        content: attr(data-label);
        text-align: left;
        font-weight: bold;
        color: #555;
        margin-right: 10px;
    }

    td:last-child {
        text-align: right;
    }

    .action-buttons {
        display: flex;
        flex-direction: row!important;
        justify-content: center;
        gap: 5px;
    }


    table{
        height: auto;
    }
}

        body {
            display: flex;
            text-align: center;
            justify-content: center;
        }

        /* Estilos da tabela responsivos */
        table {
            border-collapse: collapse;
            margin-top: 20px;
            width: auto;
        }

        th {
            padding: 8px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        td {
            padding: 8px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #b30000;
            /* Cor de fundo do cabeçalho */
            color: white;
            /* Cor do texto do cabeçalho */

        }

        /* Estilos da paginação */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }

        .pagination a,
        .pagination strong {
            margin: 0 5px;
            padding: 5px 10px;
            cursor: pointer;
            border: none;
            background-color: #fff;
            color: #000;
            border: none;
            text-decoration: none;
        }

        .pagination a:hover {
            font-weight: bold;
            background-color: #A9A9A9;
        }

        .pagination a,
        .pagination strong :active {
            background-color: dimgray;
            color: white;
        }
    

        /* Media queries para responsividade */
        
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
    /* Cor do texto */
    background-color: #c0392b;
    /* Cor de fundo */
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

.action-buttons {
    display: flex;
    /* Para que fiquem um embaixo do outro */
    gap: 8px;
    /* Espaço entre os botões */
    align-items: center;
    /* Centraliza os botões na célula */
}

        .action-buttons a {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            /* Estilos dos botões Ações */
            text-decoration: none;
            padding: 5px 8px;
            /* Tamanho fixo do padding */
            border-radius: 4px;
            color: white;
            font-weight: bold;
            background-color: #a80813;
            margin-right: 5px;
            /* Espaço entre os botões */
            transition: background-color 0.3s;
            /* Transição suave */
        }

        .btn-inativar {
            background-color: #8B0000;
        }

        /* Define a cor para este botão especifico */
        .btn-ativar {
            background-color: green;
        }

        /* Define a cor para este botão especifico */
        .btn-inativar:hover {
            background-color: #A52A2A;
        }

        /* Muda a cor ao passar o mouse */
        .btn-excluir,
        .btn-editar {
            background-color: #B22222;
        }

        .btn-editar:hover,
        .btn-excluir:hover {
            background-color: #c82333;
        }
        h1{
            font-size:30px;
        }
        body{
            height: auto;
            margin-top: 60px;
        }
    </style>

</head>

<body>
<h1>Unidades Cadastradas</h1>
    <div class="table-container">   
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Cidade</th>
                    <th>Estado</th>
                    <th>Número</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unidades as $unidade): ?>
                    <tr>
                        <td data-label="Nome"><?= htmlspecialchars($unidade['unidade_nome']) ?></td>
                        <td data-label="Cidade"><?= htmlspecialchars($unidade['unidade_cidade']) ?></td>
                        <td data-label="Estado"><?= htmlspecialchars($unidade['unidade_estado']) ?></td>
                        <td data-label="Número"><?= htmlspecialchars($unidade['unidade_numero']) ?></td>
                        <td data-label="Status"><?= htmlspecialchars($unidade['unidade_status']) ?></td>
                        <td data-label="Ações">
                            <div class="action-buttons">

                                <?php if ($unidade['unidade_status'] == 'Ativo'): ?>
                                    <a href="desativar.php?id=<?= $unidade['idunidade'] ?>" class="btn-inativar">
                                        <i class="fa fa-times"></i> Desativar
                                    </a>
                                <?php elseif ($unidade['unidade_status'] == 'Inativo'): ?>
                                    <a href="ativar.php?id=<?= $unidade['idunidade'] ?>" class="btn-ativar">
                                        <i class="fa fa-check"></i> Ativar
                                    </a>
                                <?php endif; ?>

                                <a href="editar.php?id=<?= $unidade['idunidade'] ?>" class="btn-editar">
                                    <i class="fas fa-pencil-alt"></i> Editar
                                </a>
                        </td>
        </div>
        </tr>
    <?php endforeach; ?>

    <?php if (empty($unidades)): ?>
        <tr>
            <td colspan="7">Nenhuma unidade encontrada</td>
        </tr>
    <?php endif; ?>
    </tbody>
    </table>
    </div>

    <!-- Paginação -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <a href="?pagina=<?= $i ?>" <?php if ($i == $pagina_atual)
                  echo 'class="active"'; ?>><?= $i ?></a>
        <?php endfor; ?>
    </div>

</body>

</html>