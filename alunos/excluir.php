<?php
include '../conexao.php'; // Verifique se o caminho está correto

session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Adicionando a cláusula WHERE para filtrar pela turma com o ID fornecido
    $sql = "SELECT a.idaluno, a.aluno_nome, a.aluno_matricula
            FROM aluno a
            WHERE a.idaluno = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            echo "Aluno não encontrado.";
            exit;
        }
    } catch (PDOException $e) {
        echo "Erro: " . htmlspecialchars($e->getMessage());
        exit;
    }
} else {
    echo "ID não fornecido.";
    exit;
}

$sucesso = false;
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ação de exclusão
    $sqlDelete = "DELETE FROM aluno WHERE idaluno = :id";
    try {
        $deleteStmt = $pdo->prepare($sqlDelete);
        $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $deleteStmt->execute();
        
        $sucesso = true;
    }catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $erro = "Não foi possível excluir o aluno, pois ela está associada a uma turma.";
        } else {
            $erro = "Erro ao excluir alunos: " . htmlspecialchars($e->getMessage());
        }
    }
}

require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Aluno</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: grey;
            margin: 0;
        }

        .modal {
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
        }

        .modal-content {
            background-color: #f5f5f5;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px;
            /* Ajusta o tamanho máximo para telas maiores */
            border-radius: 10px;
            box-shadow: 10px 10px 8px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.5s;
            text-align: center;
        }


        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        h2 {
            margin-top: 0;
            color: #333;
        }

        .button {
            background-color: #B30000;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            width: 100%;
            font-size: 16px;
        }

        .button:hover {
            background-color: #A52A2A;
            transform: scale(1.05);
        }

        .cancel-button, .back-button {
            display: block;
            margin-top: 10px;
            text-align: center;
            color: #B30000;
            text-decoration: none;
            font-size: 16px;
        }

        .cancel-button:hover, .back-button:hover {
            text-decoration: underline;
        }

        .success-message {
            color: green;
            font-weight: bold;
        }

        /* Responsividade para telas menores */
        @media (max-width: 600px) {
            .modal-content {
                width: 80%;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?php if ($sucesso): ?>
        <div class="modal" id="successModal">
            <div class="modal-content">
                <p class="success-message">Aluno excluído com sucesso!</p>
                <a href="consulta.php" class="back-button">Voltar para a lista de Alunos</a>
            </div>
        </div>
        <?php elseif ($erro): ?>
        <div class="modal" id="errorModal">
            <div class="modal-content">
                <p class="error-message"><?php echo $erro; ?></p>
                <a href="consulta.php" class="back-button">Voltar para a lista de alunos</a>
            </div>
        </div>
    <?php else: ?>
        <div class="modal" id="myModal">
            <div class="modal-content">
                <h2>Excluir Aluno</h2>
                <p>Você tem certeza que deseja excluir o aluno <strong><?php echo htmlspecialchars($aluno['aluno_nome']); ?></strong>?</p>
                <p>Matrícula: <?php echo htmlspecialchars($aluno['aluno_matricula']); ?></p>

                <form action="" method="post">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($aluno['idaluno']); ?>">
                    <input type="submit" class="button" value="Excluir">
                </form>
                <a href="consulta.php" class="cancel-button">Cancelar</a>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
