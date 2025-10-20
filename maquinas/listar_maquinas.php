<?php
// Inclua a conexão com o banco de dados e outras dependências necessárias
include '../conexao.php';

// Captura os filtros de pesquisa (NI ou Nome e Status)
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Configurações de paginação
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para contar o total de registros (corrigida)
    $count_sql = "SELECT COUNT(*) FROM maquina m
                  JOIN tipomaquina tm ON m.tipomaquina_id = tm.idtipomaquina
                  JOIN setor s ON m.setor_id = s.idsetor
                  WHERE 1=1";

if ($search) {
    $count_sql .= " AND (m.maquina_ni LIKE :search 
                     OR m.maquina_fabricante LIKE :search
                     OR tm.tipomaquina_nome LIKE :search)"; // Incluído tipomaquina_nome
}
if ($status_filter) {
    $count_sql .= " AND m.maquina_status = :status";
}

    $count_stmt = $pdo->prepare($count_sql);
    if ($search) {
        $count_stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }
    if ($status_filter) {
        $count_stmt->bindValue(':status', $status_filter, PDO::PARAM_STR);
    }
    $count_stmt->execute();
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // Consulta para listar máquinas com filtros
    $sql = "SELECT m.idmaquina, m.maquina_ni, tm.tipomaquina_nome, s.setor_nome, 
                   m.maquina_status, m.maquina_peso, m.maquina_fabricante, 
                   m.maquina_modelo, m.maquina_ano, m.maquina_capacidade
            FROM maquina m 
            JOIN tipomaquina tm ON m.tipomaquina_id = tm.idtipomaquina
            JOIN setor s ON m.setor_id = s.idsetor 
            WHERE 1=1";

if ($search) {
    $sql .= " AND (m.maquina_ni LIKE :search 
                 OR m.maquina_fabricante LIKE :search
                 OR tm.tipomaquina_nome LIKE :search)"; // Incluído tipomaquina_nome
}
if ($status_filter) {
    $sql .= " AND m.maquina_status = :status";
}

$sql .= " ORDER BY tm.tipomaquina_nome ASC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);

    if ($search) {
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }
    if ($status_filter) {
        $stmt->bindValue(':status', $status_filter, PDO::PARAM_STR);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao buscar máquinas: " . $e->getMessage();
    exit;
}

// Renderiza a tabela de resultados
if (!empty($maquinas)): ?>
    <table class="table" id="machinesTable">
        <thead class="TH">
            <tr class="TRH">
                <th class="th">Máquina NI</th>
                <th class="th">Tipo de Máquina</th>
                <th class="th">Setor</th>
                <th class="th">Peso</th>
                <th class="th">Fabricante</th>
                <th class="th">Modelo</th>
                <th class="th">Ano</th>
                <th class="th">Capacidade</th>
                <th class="th">Status</th>
                <th class="th">Ação</th>
            </tr>
        </thead>
        <tbody class="TB">
            <?php foreach ($maquinas as $maquina): ?>
                <tr class="TRB">
                    <td class="tb" data-label="NI"><?= htmlspecialchars($maquina['maquina_ni']) ?></td>
                    <td class="tb" data-label="Nome"><?= htmlspecialchars($maquina['tipomaquina_nome']) ?></td>
                    <td class="tb" data-label="Setor"><?= htmlspecialchars($maquina['setor_nome']) ?></td>
                    <td class="tb" data-label="Peso"><?= htmlspecialchars($maquina['maquina_peso']." Kg") ?></td>
                    <td class="tb" data-label="Fabricante"><?= htmlspecialchars($maquina['maquina_fabricante']) ?></td>
                    <td class="tb" data-label="Modelo"><?= htmlspecialchars($maquina['maquina_modelo']) ?></td>
                    <td class="tb" data-label="Ano"><?= htmlspecialchars($maquina['maquina_ano']) ?></td>
                    <td class="tb" data-label="Capacidade"><?= htmlspecialchars($maquina['maquina_capacidade']) ?></td>
                    <td class="tb" data-label="Status"><?= htmlspecialchars($maquina['maquina_status']) ?></td>
                    <td class="action-buttons"  data-label="Ações">
                        <a href="toggle_status.php?id=<?= $maquina['idmaquina'] ?>" class="btn-inativar">
                            <?= ($maquina['maquina_status'] === 'Ativo') ? '<i class="fa fa-times"></i> Desativar' : '<i class="fa fa-check"></i> Ativar' ?>
                        </a>
                        <a href="editar.php?id=<?= $maquina['idmaquina'] ?>" class="btn-editar"><i class="fas fa-pencil-alt"></i> Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Paginação -->
  <!-- Paginação -->
  <div class="pagination">
    <?php
    $range = 2; // Número de páginas a mostrar antes e depois da página atual

    // Links das páginas
    for ($i = max(1, $page - $range); $i <= min($total_pages, $page + $range); $i++) {
        echo '<a href="?page=' . $i . '&search=' . urlencode($search) . '&status=' . urlencode($status_filter) . '" class="' . ($i == $page ? 'active' : '') . '">' . $i . '</a>';
    }

    ?>
</div>


<?php else: ?>
    <p>Nenhuma máquina encontrada para os critérios especificados.</p>
<?php endif; ?>
