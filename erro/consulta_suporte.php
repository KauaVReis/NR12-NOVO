<?php
// Conexão com o banco de dados
$host = 'localhost';
$dbname = 'nr12';
$username = 'root';
$password = '';
date_default_timezone_set('America/Sao_Paulo');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Função para calcular a quantidade total de registros
function getTotalDefeitos($pdo, $busca = '')
{
    $query = "SELECT COUNT(*) FROM defeitos WHERE descricao LIKE :busca OR colaborador_id LIKE :busca OR aluno_id LIKE :busca";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':busca', '%' . $busca . '%');
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Função para obter os resultados da pesquisa
function getDefeitos($pdo, $limite, $offset, $busca = '')
{
    $query = "
        SELECT defeitos.id, defeitos.descricao, 
               colaborador.colaborador_nome, 
               aluno.aluno_nome, 
               maquina.maquina_ni,
               defeitos.data_registro, defeitos.requisitos_ids 
        FROM defeitos
        LEFT JOIN colaborador ON defeitos.colaborador_id = colaborador.idcolaborador
        LEFT JOIN aluno ON defeitos.aluno_id = aluno.idaluno
        LEFT JOIN maquina ON defeitos.maquina_id = maquina.idmaquina
        WHERE defeitos.descricao LIKE :busca 
           OR colaborador.colaborador_nome LIKE :busca 
           OR aluno.aluno_nome LIKE :busca 
           OR maquina.maquina_ni LIKE :busca
        LIMIT :limite OFFSET :offset
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':busca', '%' . $busca . '%');
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Recebe a busca
$busca = isset($_GET['busca']) ? $_GET['busca'] : '';

// Paginação
$limite = 10; // Número de registros por página
$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset = ($pagina - 1) * $limite; // Deslocamento para a consulta

// Total de registros e páginas
$totalDefeitos = getTotalDefeitos($pdo, $busca);
$totalPaginas = ceil($totalDefeitos / $limite);

// Consulta de resultados
$defeitos = getDefeitos($pdo, $limite, $offset, $busca);

// Função para gerar as páginas de forma dinâmica
function gerarPaginas($paginaAtual, $totalPaginas)
{
    $paginacao = [];
    $paginacao[] = 1;
    if ($paginaAtual > 2) {
        $paginacao[] = $paginaAtual - 1;
    }
    if ($paginaAtual > 1 && $paginaAtual < $totalPaginas) {
        $paginacao[] = $paginaAtual;
    }
    if ($paginaAtual < $totalPaginas - 1) {
        $paginacao[] = $paginaAtual + 1;
    }
    if ($totalPaginas > 1 && !in_array($totalPaginas, $paginacao)) {
        $paginacao[] = $totalPaginas;
    }
    return $paginacao;
}

$paginacao = gerarPaginas($pagina, $totalPaginas);

// Gerar o HTML da tabela e paginação
$html = '';

$html .= '<table class="TabelaErro">';
$html .= '<thead><tr>
                <th>ID</th>
                <th>Descrição</th>
                <th>Colaborador ID</th>
                <th>Aluno ID</th>
                <th>Máquina ID</th>
                <th>Data de Registro</th>
                <th>Requisitos IDs</th>
            </tr></thead>';
$html .= '<tbody>';

foreach ($defeitos as $defeito) {
    $html .= '<tr>';
    $html .= '<td>' . $defeito['id'] . '</td>';
    $html .= '<td>' . $defeito['descricao'] . '</td>';
    $html .= '<td>' . $defeito['colaborador_id'] . '</td>';
    $html .= '<td>' . $defeito['aluno_id'] . '</td>';
    $html .= '<td>' . $defeito['maquina_id'] . '</td>';
    $html .= '<td>' . $defeito['data_registro'] . '</td>';
    $html .= '<td>' . $defeito['requisitos_ids'] . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table>';

$html .= '<div id="paginationControls">';
foreach ($paginacao as $paginaNumero) {
    if ($paginaNumero == $pagina) {
        $html .= '<span class="pagination-button current-page">' . $paginaNumero . '</span>';
    } else {
        $html .= '<a href="?pagina=' . $paginaNumero . '&busca=' . urlencode($busca) . '" class="pagination-button">' . $paginaNumero . '</a>';
    }
}
$html .= '</div>';

echo $html;
?>