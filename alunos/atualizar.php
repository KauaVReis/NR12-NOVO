<?php
session_start(); // Inicia a sessão
include '../conexao.php'; // Verifique se o caminho está correto

// Verifica se os dados foram enviados pelo formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $aluno_nome = $_POST['aluno_nome'];
    $aluno_matricula = $_POST['aluno_matricula'];
    $turmas_id = $_POST['turmas_id'];

    // Verifica se a matrícula já existe para outro aluno (exceto o aluno atual)
    $sqlCheckMatricula = "SELECT COUNT(*) FROM aluno WHERE aluno_matricula = :matricula AND idaluno != :id";
    $stmtCheckMatricula = $pdo->prepare($sqlCheckMatricula);
    $stmtCheckMatricula->bindParam(':matricula', $aluno_matricula);
    $stmtCheckMatricula->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtCheckMatricula->execute();

    if ($stmtCheckMatricula->fetchColumn() > 0) {
        // Se a matrícula já existir, define a mensagem de erro
        $_SESSION['mensagem'] = 'Erro: Matrícula já cadastrada para outro aluno!';
        $_SESSION['mensagem_tipo'] = 'erro'; // Define o tipo da mensagem (erro)
        header('Location: alterar.php?id=' . $id); // Redireciona para o formulário de edição
        exit;
    }

    // Caso a matrícula não seja duplicada, prossegue com a atualização
    $sql = "UPDATE aluno SET aluno_nome = :aluno_nome, aluno_matricula = :aluno_matricula, turmas_id = :turmas_id WHERE idaluno = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':aluno_nome', $aluno_nome);
        $stmt->bindParam(':aluno_matricula', $aluno_matricula);
        $stmt->bindParam(':turmas_id', $turmas_id);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Define a mensagem de sucesso na sessão
            $_SESSION['mensagem'] = "Aluno atualizado com sucesso!";
            $_SESSION['mensagem_tipo'] = "sucesso"; // Define o tipo da mensagem
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar aluno.";
            $_SESSION['mensagem_tipo'] = "erro";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro: " . htmlspecialchars($e->getMessage());
        $_SESSION['mensagem_tipo'] = "erro";
    }
}

// Exibe a mensagem em uma página com modal
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Resultado da Atualização</title>
    <link rel="stylesheet" href="../../nr12/style.css"> <!-- Ajuste conforme seu estilo -->
    <style>
        .modal {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal.sucesso {
            color: green;
        }
        .modal.erro {
            color: red;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="modal <?php echo htmlspecialchars($_SESSION['mensagem_tipo']); ?>">
        <div class="modal-content">
            <p><?php echo htmlspecialchars($_SESSION['mensagem']); ?></p>
   
        </div>
    </div>
    <script>
        setTimeout(() => {
            window.location.href = "consulta.php";
        }, 750); // Redireciona após 3 segundos
    </script>
</body>
</html>
<?php
unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']); // Limpa a mensagem da sessão
?>
