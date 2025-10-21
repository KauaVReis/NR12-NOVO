<?php
// Inclua as configurações e conexões necessárias
include "../conexao.php";
require_once  __DIR__ . '/../vendor/autoload.php'; // Certifique-se de ajustar o caminho corretamente

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$filtro_unico = isset($_POST['filtro']) ? trim($_POST['filtro']) : '';
$filtro_data = isset($_POST['filtro_data']) ? trim($_POST['filtro_data']) : '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT 
        h.historicoid,
        h.historico_data,
        h.historico_hora,
        h.historico_status,
        m.maquina_ni AS maquina_ni,
        a.aluno_nome AS aluno_nome,
        c.colaborador_nome AS colaborador_nome,
        r.requisito_topico AS requisito_topico,
        r.tipo_req AS tipo_req
    FROM historico h
    LEFT JOIN maquina m ON h.maquina_id = m.idmaquina
    LEFT JOIN aluno a ON h.aluno_id = a.idaluno
    LEFT JOIN colaborador c ON h.colaborador_id = c.idcolaborador
    LEFT JOIN requisitos r ON h.requisito_id = r.idrequisitos
    WHERE 1=1";

    // Adicionar filtros à consulta
    if (!empty($filtro_unico)) {
        $sql .= " AND (
            a.aluno_nome LIKE :filtro OR 
            m.maquina_ni LIKE :filtro OR 
            h.historico_data LIKE :filtro OR
            c.colaborador_nome LIKE :filtro
        )";
    }

    if (!empty($filtro_data)) {
        $sql .= " AND h.historico_data = :filtro_data";
    }

    $stmt = $conn->prepare($sql);

    // Vincular parâmetros, se necessários
    if (!empty($filtro_unico)) {
        $stmt->bindValue(':filtro', '%' . $filtro_unico . '%', PDO::PARAM_STR);
    }

    if (!empty($filtro_data)) {
        $stmt->bindValue(':filtro_data', $filtro_data, PDO::PARAM_STR);
    }

    $stmt->execute();
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Criação do arquivo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Cabeçalhos
    $sheet->setCellValue('A1', 'NI Máquina');
    $sheet->setCellValue('B1', 'Aluno');
    $sheet->setCellValue('C1', 'Professor');
    $sheet->setCellValue('D1', 'Data');
    $sheet->setCellValue('E1', 'Hora');
    $sheet->setCellValue('F1', 'Tópico');
    $sheet->setCellValue('G1', 'Tipo Req');

    // Preencher os dados
    $linha = 2;
    foreach ($dados as $row) {
        $sheet->setCellValue('A' . $linha, $row['maquina_ni']);
        $sheet->setCellValue('B' . $linha, $row['aluno_nome']);
        $sheet->setCellValue('C' . $linha, $row['colaborador_nome']);
        $sheet->setCellValue('D' . $linha, date('d/m/Y', strtotime($row['historico_data'])));
        $sheet->setCellValue('E' . $linha, date('H:i', strtotime($row['historico_hora'])));
        $sheet->setCellValue('F' . $linha, $row['requisito_topico']);
        $sheet->setCellValue('G' . $linha, $row['tipo_req']);
        $linha++;
    }

    // Download do arquivo
    $writer = new Xlsx($spreadsheet);
    $nome_arquivo = 'historico_filtrado.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nome_arquivo . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
} catch (PDOException $e) {
    echo "Erro ao gerar Excel: " . $e->getMessage();
}
