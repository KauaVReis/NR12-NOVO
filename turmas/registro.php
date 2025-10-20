<?php
// Inclui a conexão com o banco de dados
include '../conexao.php'; // Certifique-se de que o caminho está correto

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe os dados do formulário
    $nome = $_POST['turma_nome'];
    $email = $_POST['periodo_turma'];
    $inicio_turma = $_POST['inicio_turma'];
    $ano_inicio = date('Y', strtotime($inicio_turma));
    $setor_id = $_POST['fim_turma'];
    $curso_id = $_POST['curso_id'];
    $colaborador_id = $_POST['colaborador_id'];

    $nome = $nome . " - " . $ano_inicio;

    // Verifica se o nome da turma já existe no banco de dados
    $checkSql = "SELECT COUNT(*) FROM turmas WHERE turma_nome = :nome";
    $stmtCheck = $pdo->prepare($checkSql);
    $stmtCheck->bindParam(':nome', $nome);
    $stmtCheck->execute();
    $turmaExists = $stmtCheck->fetchColumn();

    if ($turmaExists) {
        echo json_encode([
            "status" => "erro",
            "mensagem" => "Esta turma já está cadastrada."
        ]);
        exit();
    } else {
        // Prepara a consulta de inserção
        $sql = "INSERT INTO turmas (turma_nome, turma_periodo, turma_inicio, turma_fim, curso_id, colaborador_id) 
                VALUES (:nome, :email, :inicio_turma, :setor_id, :curso_id, :colaborador_id)";

        try {
            // Prepara a declaração
            $stmt = $pdo->prepare($sql);

            // Bind dos parâmetros
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':inicio_turma', $inicio_turma);
            $stmt->bindParam(':setor_id', $setor_id);
            $stmt->bindParam(':curso_id', $curso_id);
            $stmt->bindParam(':colaborador_id', $colaborador_id);

            // Executa a consulta
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Cadastro realizado com sucesso!"
                ]);
                exit();
            } else {
                echo json_encode([
                    "status" => "erro",
                    "mensagem" => "Erro ao cadastrar: " . implode(", ", $stmt->errorInfo())
                ]);
                exit();
            }
        } catch (PDOException $e) {
            echo json_encode([
                "status" => "erro",
                "mensagem" => "Erro ao cadastrar: " . $e->getMessage()
            ]);
            exit();
        }
    }
} else {
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Nenhum dado recebido."
    ]);
    exit();
}

// Fecha a conexão (opcional com PDO)
$pdo = null;
?>