<?php
include '../conexao.php';

$registros_por_pagina = 3;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

$pesquisa = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : '';

$sql_total = "SELECT COUNT(*) FROM tipomaquina WHERE tipomaquina_nome LIKE :pesquisa";
$stmt_total = $pdo->prepare($sql_total);
$stmt_total->execute(['pesquisa' => '%' . $pesquisa . '%']);
$total_registros = $stmt_total->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);

$sql = "SELECT * FROM tipomaquina WHERE tipomaquina_nome LIKE :pesquisa LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':pesquisa', '%' . $pesquisa . '%', PDO::PARAM_STR);
$stmt->bindValue(':limite', $registros_por_pagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    echo "<table class='tabela'>";
    echo "<tr>
        <th>Nome</th>
        <th>Status</th>
        <th>Editar</th>
        <th>Ativar/Desativar</th>
        <th>Excluir</th>
    </tr>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['tipomaquina_nome']) . "</td>";
        echo "<td>" . htmlspecialchars($row['tipomaquina_status']) . "</td>";
        echo "<td><a href='editar.php?idtipomaquina=" . htmlspecialchars($row['idtipomaquina']) . "'>Editar</a></td>";
        echo "<td><a href='desativar.php?id=" . htmlspecialchars($row['idtipomaquina']) . "'>Desativar</a></td>";
        echo "<td><a href='excluir.php?id=" . htmlspecialchars($row['idtipomaquina']) . "'>Excluir</a></td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<div class='paginacao'>";
    for ($i = 1; $i <= $total_paginas; $i++) {
        echo "<a href='?pagina=$i&pesquisa=$pesquisa'>$i</a>";
    }
    echo "</div>";
} else {
    echo "<p>Nenhum resultado encontrado.</p>";
}
?>
