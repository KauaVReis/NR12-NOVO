<?php
include '../conexao.php';

if (isset($_GET['idTurma'])) {
    $idTurma = $_GET['idTurma'];
    $alunos = buscarAlunosDaTurma($pdo, $idTurma);  // Use sua função existente
    echo json_encode(['alunos' => $alunos]);
}

function buscarAlunosDaTurma($pdo, $idTurma)
{ // Sua função buscarAlunosDaTurma() original
    $sql = "SELECT aluno_nome, aluno_matricula, aluno_status FROM aluno WHERE turmas_id = :idTurma";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':idTurma', $idTurma, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>