<?php
// Inclua a conexão com o banco de dados
include '../conexao.php';  // Certifique-se de que o caminho está correto

// Inicie a sessão
session_start();

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pega os valores enviados pelo formulário
    $matricula = $_POST['matricula'];
    $nimaquina = $_POST['nimaquina'];

    try {
        // Prepara a consulta para verificar se a matrícula existe e obter a turma do aluno
        $sqlAluno = "
            SELECT aluno.*, turmas.idturmas
            FROM aluno 
            INNER JOIN turmas ON aluno.turmas_id = turmas.idturmas 
            WHERE aluno.aluno_matricula = :matricula
        ";
        $stmtAluno = $pdo->prepare($sqlAluno);
        $stmtAluno->bindParam(':matricula', $matricula);
        $stmtAluno->execute();

        // Verifica se a matrícula foi encontrada
        if ($stmtAluno->rowCount() > 0) {
            $aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC); // Obtém os dados do aluno

            // Verifica o NI da máquina
            $sqlMaquina = "SELECT idmaquina, maquina_ni FROM maquina WHERE maquina_ni = :nimaquina AND maquina_status = 'Ativo' ";
            $stmtMaquina = $pdo->prepare($sqlMaquina);
            $stmtMaquina->bindParam(':nimaquina', $nimaquina);
            $stmtMaquina->execute();

            if ($stmtMaquina->rowCount() > 0) {
                // Se a máquina for encontrada, armazena os dados na sessão
                $maquina = $stmtMaquina->fetch(PDO::FETCH_ASSOC);
                $_SESSION['matricula'] = $matricula;
                $_SESSION['nimaquina'] = $maquina['maquina_ni'];
                $_SESSION['idmaquina'] = $maquina['idmaquina'];

                // Redireciona para cadastro.php
                header("Location: ../historico/menualuno.php");
                exit;
            } else {
                $erro = "Máquina não encontrada ou em manutenção. Tente novamente mais tarde.";
            }
        } else {
            $erro = "Matricula não encontrada. Tente novamente mais tarde.";
        }
    } catch (PDOException $e) {
        $erro = "Erro ao verificar matrícula e NI da máquina: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilos.css">
    <title>Verificação de Matrícula e Máquina</title>
</head>

<body class="verificar-colaborador">
    <div class="container-verif-colab">
        <h1 class="titulo-verif-colab">Verificação de Matrícula e Máquina</h1>

        <?php if (isset($erro)): ?>
            <p class="message"><?php echo $erro; ?></p>
        <?php endif; ?>

        <button type="submit" class="back-button" onclick="voltar()">Voltar para Login</button>
    </div>

    <script>
        function voltar() {
            window.location.href = 'entrecomoaluno.php'
        }
    </script>
</body>

</html>