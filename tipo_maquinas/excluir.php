<?php
include '../conexao.php'; // Verifique se o caminho está correto

session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Adicionando a cláusula WHERE para filtrar pela turma com o ID fornecido
    $sql = "SELECT t.idtipomaquina, t.tipomaquina_nome
            FROM tipomaquina t
            WHERE t.idtipomaquina = :id";

    try {
        $stmt = $pdo->prepare($sql);
        // Vincule o parâmetro :id
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $motor = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            echo "Tipo de Máquina não encontrado.";
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ação de exclusão
    $sqlDelete = "DELETE FROM tipomaquina WHERE idtipomaquina = :id";
    try {
        $deleteStmt = $pdo->prepare($sqlDelete);
        $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $deleteStmt->execute();
        $sucesso = true;

        echo "Tipo de Máquina excluído com sucesso!";
        echo '<a href="consulta.php">Voltar para a lista de Tipo de Máquina</a>';
        exit;
    } catch (PDOException $e) {
        echo "Erro ao excluir Tipo de Máquina: " . htmlspecialchars($e->getMessage());
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Excluir Tipo de Máquina</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
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
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
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

    .cancel-button,
    .back-button {
        display: block;
        margin-top: 10px;
        text-align: center;
        color: #B30000;
        text-decoration: none;
        font-size: 16px;
    }

    .cancel-button:hover,
    .back-button:hover {
        text-decoration: underline;
    }

    .success-message {
        color: green;
        font-weight: bold;
    }
    @media (max-width: 498px) {
        .modal-content {
            width: 80%; /* Aumenta para quase toda a largura da tela */
    
        }
    }
</style>
<body>
    <?php if ($sucesso): ?>
        <div class="modal" id="successModal">
            <div class="modal-content">
                <p class="success-message">Motor excluído com sucesso!</p>
                <a href="consulta.php" class="back-button">Voltar para a lista de Motor</a>
            </div>
        </div>
    <?php else: ?>
        <div class="modal" id="myModal">
            <div class="modal-content">
                <h2>Excluir Tipo de Máquina</h2>
                <p>Você tem certeza que deseja excluir o Tipo de Máquina:
                    <strong><?php echo htmlspecialchars($motor['tipomaquina_nome']); ?></strong>?
                </p>


                <form action="" method="post">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($motor['idtipomaquina']); ?>">
                    <input type="submit" class="button" value="Excluir">
                </form>
                <a href="consulta.php" class="cancel-button">Cancelar</a>
            </div>
        </div>
    <?php endif; ?>

</body>

</html>