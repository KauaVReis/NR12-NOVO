<?php
session_start();
require_once '../verifica_permissao.php';
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
define('BASE_URL', '../../nr12/');

include '../conexao.php';
include $_SERVER['DOCUMENT_ROOT'] . '/nr12/sidebar.php';

if (isset($_GET['id']) && isset($_GET['acao']) && $_GET['acao'] == 'Realizada') {
    $idmanutencao = $_GET['id'];
    $data_realizada = date("Y-m-d H:i:s");

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT m.intervalo_manutencao 
            FROM manutencao man
            JOIN maquina m ON man.maquina_id = m.idmaquina
            WHERE man.idmanutencao = :idmanutencao
        ");
        $stmt->bindValue(":idmanutencao", $idmanutencao);
        $stmt->execute();
        $intervalo = $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            UPDATE manutencao 
            SET manutencao_realizada = :data_realizada, manutencao_estado = 'Consertado'
            WHERE idmanutencao = :idmanutencao
        ");
        $stmt->bindValue(':data_realizada', $data_realizada);
        $stmt->bindValue(":idmanutencao", $idmanutencao);
        $stmt->execute();

        if ($intervalo) {
            $meses = 0;
            switch ($intervalo) {
                case '3':
                    $meses = 3;
                    break;
                case '6':
                    $meses = 6;
                    break;
                case '12':
                    $meses = 12;
                    break;
            }

            $proxima_manutencao = date('Y-m-d', strtotime("+$meses months", strtotime($data_realizada)));

            $stmt = $pdo->prepare("
                UPDATE maquina 
                SET data_proxima_manutencao = :proxima_manutencao, maquina_status = 'Ativo' 
                WHERE idmaquina = (
                    SELECT maquina_id FROM manutencao WHERE idmanutencao = :idmanutencao
                ) AND maquina_status = 'Inativo'
            ");
            $stmt->bindValue(":idmanutencao", $idmanutencao);
            $stmt->bindValue(":proxima_manutencao", $proxima_manutencao);
            $stmt->execute();
        }

        $pdo->commit();
        echo "<script>window.location.href='tabela.php';</script>";
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<div class='error-message'>Erro ao atualizar a manutenção: " . $e->getMessage() . "</div>";
    }
}

$query = "
    SELECT 
        m.idmanutencao,
        m.manutencao_data,
        maq.maquina_ni AS maquina_ni,
        c.colaborador_nome AS colaborador_nome,
        m.manutencao_estado,
        m.manutencao_descricao,
        m.tipo_manutencao,
        m.manutencao_realizada,
        m.manutencao_status
    FROM manutencao m
    LEFT JOIN maquina maq ON m.maquina_id = maq.idmaquina
    LEFT JOIN colaborador c ON m.colaborador_id = c.idcolaborador
    WHERE 1=1 
";

if (isset($_GET['status']) && ($_GET['status'] == "Ativo" || $_GET['status'] == "Inativo")) {
    $status = $_GET['status'];
    $query .= " AND m.manutencao_status = :status";
}

$stmt = $pdo->prepare($query);

if (isset($status) && ($status == "Ativo" || $status == "Inativo")) {
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
}

$stmt->execute();
$manutencoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Manutenções</title>
    <link rel="stylesheet" href="../CSS/estilos.css">
</head>

<div class="container">
    <div class="header-table">

        <div class="search-bar-container">
            <h1>Consulta Manutenção </h1>
            <form class="search-form">
                <input class="search-input consultafuncionario" type="text" id="searchInput"
                    placeholder="Pesquisar por Data, Máquina(NI), Colaborador, Estado, Descrição, Tipo de Manutenção, Data realizada e status" />
                    <select id="statusFilter" class="status-filter" onchange="aplicarFiltro()">
                <option value="">Todos</option>
                <option value="Ativo" <?php echo ($statusFilter === 'Ativo') ? 'selected' : ''; ?>>Ativo</option>
                <option value="Inativo" <?php echo ($statusFilter === 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
            </select>
            </form>
         
        </div>
    </div>
    <div class="tabela-container">
        <table class="table_manu" id="tabelaManutencao">
            <thead class="table-hd_manu">
                <tr>
                    <th class="table-th_manu">Data</th>
                    <th class="table-th_manu">Máquina (NI)</th>
                    <th class="table-th_manu">Colaborador</th>
                    <th class="table-th_manu">Estado</th>
                    <th class="table-th_manu">Descrição</th>
                    <th class="table-th_manu">Tipo de Manutenção</th>
                    <th class="table-th_manu">Data Realizada</th>
                    <th class="table-th_manu">Status</th>
                    <?php if (in_array($_SESSION['colaborador_permissao'], ['Adm', 'Coordenador', 'manutencao'])): ?>
                        <th class="table-th_manu">Ações</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="table-bd_manu" id="corpoTabela">
                <?php foreach ($manutencoes as $manutencao): ?>
                    <tr class="table-tr_manu">
                        <td class="table-td_manu" data-label="Data"><?= htmlspecialchars($manutencao['manutencao_data']) ?>
                        </td>
                        <td class="table-td_manu" data-label="Máquina (NI)">
                            <?= htmlspecialchars($manutencao['maquina_ni']) ?>
                        </td>
                        <td class="table-td_manu" data-label="Colaborador">
                            <?= htmlspecialchars($manutencao['colaborador_nome']) ?>
                        </td>
                        <td class="table-td_manu" data-label="Estado">
                            <?= htmlspecialchars($manutencao['manutencao_estado']) ?>
                        </td>
                        <td class="table-td_manu" data-label="Descrição">
                            <?= htmlspecialchars($manutencao['manutencao_descricao']) ?>
                        </td>
                        <td class="table-td_manu" data-label="Tipo de Manutenção">
                            <?= htmlspecialchars($manutencao['tipo_manutencao']) ?>
                        </td>
                        <td class="table-td_manu" data-label="Data Realizada"><span
                                class="data_realizada"><?= htmlspecialchars($manutencao['manutencao_realizada']) ?></span>
                        </td>
                        <td class="table-td_manu" data-label="Status">
                            <?= htmlspecialchars($manutencao['manutencao_status']) ?>
                        </td>
                        <td class="action-buttons" data-label="Ações">
                            <?php if (in_array($_SESSION['colaborador_permissao'], ['Adm', 'Coordenador'])): ?>
                                <?php if ($manutencao['manutencao_status'] === 'Ativo'): ?>
                                    <a href="status.php?id=<?= $manutencao['idmanutencao'] ?>&acao=Inativar" class="btn-inativar"><i
                                            class="fa fa-times"></i> Inativar</a>
                                <?php else: ?>
                                    <a href="status.php?id=<?= $manutencao['idmanutencao'] ?>&acao=Ativar" class="btn-inativar"><i
                                            class="fa fa-check"></i> Ativar</a>
                                <?php endif; ?>
                                <?php if ($manutencao['manutencao_estado'] !== 'Consertado'): ?>
                                    <a href="tabela.php?id=<?= $manutencao['idmanutencao'] ?>&acao=Realizada"
                                        class="btn-consertado"><i class="fa fa-check"></i> Consertado</a>
                                <?php endif; ?>
                                <!-- Botão Excluir (redireciona para confirmar exclusão) -->
                                <a href="excluir.php?id=<?= $manutencao['idmanutencao'] ?>" class="btn-excluir"><i
                                        class="fa fa-trash"></i> Excluir</a>
                            <?php endif; ?>
                        </td>


                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="pagination" id="pagination"></div>
    </div>
</div>
<?php if (isset($_SESSION['message'])): ?>
    <div class="message"><?= $_SESSION['message'] ?></div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>


<script>
    const linhasPorPagina = 10;  // Definido para 10 itens por página
    let paginaAtual = 1;

    function exibirPagina(pagina) {
        const tabela = document.getElementById("corpoTabela");
        const linhas = tabela.getElementsByTagName("tr");

        const inicio = (pagina - 1) * linhasPorPagina;
        const fim = inicio + linhasPorPagina;

        for (let i = 0; i < linhas.length; i++) {
            linhas[i].style.display = (i >= inicio && i < fim) ? "" : "none";
        }

        atualizarPaginacao();
    }

    function atualizarPaginacao() {
        const tabela = document.getElementById("corpoTabela");
        const linhas = tabela.getElementsByTagName("tr");
        const totalPaginas = Math.ceil(linhas.length / linhasPorPagina);
        const paginationDiv = document.getElementById("pagination");
        paginationDiv.innerHTML = "";

        for (let i = 1; i <= totalPaginas; i++) {
            const link = document.createElement("a");
            link.innerHTML = i;
            link.href = "#";
            link.classList.add("pagina-link");
            if (i === paginaAtual) link.classList.add("active");

            link.addEventListener("click", function (event) {
                event.preventDefault();
                paginaAtual = i;
                exibirPagina(i);
            });
            paginationDiv.appendChild(link);
        }
    }

    function aplicarFiltro() {
        var status = document.getElementById("statusFilter").value;
        window.location.href = "?status=" + status;
    }

    // Filtro de pesquisa
    document.getElementById('searchInput').addEventListener('input', function () {
        const filter = this.value.toUpperCase();
        const tabela = document.getElementById('corpoTabela');
        const linhas = tabela.getElementsByTagName('tr');

        for (let i = 0; i < linhas.length; i++) {
            const colunas = linhas[i].getElementsByTagName('td');
            let encontrado = false;
            for (let j = 0; j < colunas.length; j++) {
                const textoColuna = colunas[j].textContent || colunas[j].innerText;
                if (textoColuna.toUpperCase().indexOf(filter) > -1) {
                    encontrado = true;
                    break;
                }
            }
            linhas[i].style.display = encontrado ? "" : "none";
        }

        atualizarPaginacao();
    });

    window.onload = function () {
        exibirPagina(paginaAtual);
    };

    let deleteUrl = '';

    // Função para abrir o modal de confirmação e definir a URL de exclusão
    function openConfirmModal(idmanutencao) {
        deleteUrl = `excluir.php?id=${idmanutencao}`;
        document.getElementById('confirmModal').classList.add('show');
    }

    // Função para confirmar a exclusão e redirecionar para a URL de exclusão
    function confirmDelete() {
        window.location.href = deleteUrl;
    }

    // Função para fechar o modal
    function closeModal() {
        document.getElementById('confirmModal').classList.remove('show');
    }

</script>
</body>

</html>

<style>
    .search-form{
        display: flex;
        flex-direction: row;
        gap: 25px;
        justify-content: center;
        align-items: center;
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

    #navegacaoPaginacao {
        margin-top: 20px;
        text-align: center;
    }

    #navegacaoPaginacao button {
        text-decoration: none;
        padding: 5px 10px;
        color: black;
        margin: 0 3px;
        transition: background-color 0.3s;
        background-color: #fff;
        border: none;
        cursor: pointer;
    }

    #navegacaoPaginacao button:hover {
        background-color: gray;
        font-weight: bold;
    }

    .header-table {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .action-buttons {
        display: masonry;
        gap: 5px;
        justify-content: center;
    }

    .btn-editar,
    .btn-inativar,
    .btn-realizada {
        font-size: 14px;
        padding: 5px;
    }

    .table_manu {
        width: 100%;
        margin-top: 10px;
    }

    .table-th_manu {
        background-color: #b30000;
        color: white;
        text-align: left;
    }

    .table-td_manu {
        border-bottom: 1px solid #ddd;
        text-align: left;
    }

    .table-th_manu,
    .table-td_manu {
        text-align: center;
        font-size: 15px;
    }

    #navegacaoPaginacao a {
        text-decoration: none;
        padding: 4px 8px;
        color: black;
        margin: 0 3px;
        transition: background-color 0.3s;
        background-color: #fff;
    }

    #navegacaoPaginacao a:hover {
        background-color: gray;
        font-weight: bold;
    }

    .pagination {
        margin-top: 20px;
        text-align: center;
    }

    .pagination a {
        text-decoration: none;
        padding: 4px 8px;
        color: black;
        margin: 0 3px;
        transition: background-color 0.3s;
        background-color: #fff;
    }

    .pagination a:hover {
        background-color: gray;
        font-weight: bold;
    }

    .pagination a.active {
        background-color: dimgray;
        color: white;
    }

    .btn-consertado {
        background-color: #b30000;
        border: 1px solid black;
    }
    .search-bar-container{
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    /* Estilo para dispositivos menores que 480px */
    @media (max-width: 670px) {
        .tabela-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        body {
            height: auto;
        }

        h1 {
            margin-top: 50px;
            font-size: 1.7em;
            /* Reduz o tamanho do título no mobile */
            line-height: 1.4;
            /* Aumenta o espaçamento entre linhas */
            text-align: center;
            padding: 5px;

        }

        .header-table {
            width: 100%;
            display: flex;
            flex-direction: column;
            /* Organiza os elementos em coluna */
            align-items: center;
            padding: 10px;
            gap: 10px;
        }

        .action-buttons {
            display: flex;
            flex-direction: row;
            margin-left: 5%;
            gap: 20px;
            justify-content: space-between;
        }

        .action-buttons a {
        font-size: 12px;  /* Reduz o tamanho da fonte */
        padding: 6px 8px;  /* Reduz o padding dos botões */
    }

        label {
            margin-top: 10px !important;

        }
        #searchInput{
            width: 270px;
        }
        form{
            margin: auto;
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

        .table_manu {
            width: 90%;
            margin-top: 10px;
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

        .table_manu,
        .table-tr_manu,
        .table-bd_manu {
            display: block;
        }

        .table-th_manu {
            display: none;
        }

        .table-td_manu {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 0.9em;
        }

        .table-td_manu:before {
            content: attr(data-label);
            font-weight: bold;
            color: #555;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            margin-top: 10px;
        }

        .btn-inativar,
        .btn-ativar {
            font-size: 0.8em;
            /* Ajusta o tamanho da fonte especificamente para os botões de inativar e ativar */
            padding: 4px 8px;
            /* Ajusta o padding */
        }
        .search-form{
            flex-direction: column;
        }
    }

</style>