<?php
include '../conexao.php';

$termo_busca = isset($_GET['query']) ? $_GET['query'] : '';

try {
    $sql = "SELECT a.idaluno, a.aluno_nome, a.aluno_matricula, t.turma_nome, a.aluno_status
            FROM aluno a
            JOIN turmas t ON a.turmas_id = t.idturmas
            WHERE a.aluno_nome LIKE :busca OR a.aluno_matricula LIKE :busca 
                  OR t.turma_nome LIKE :busca OR a.aluno_status LIKE :busca";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':busca', "%$termo_busca%");
    $stmt->execute();

    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($alunos);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erro ao buscar alunos: " . $e->getMessage()]);
}
?>