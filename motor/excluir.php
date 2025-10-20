<?php
include '../conexao.php';

session_start();
require_once '../verifica_permissao.php';
verificaPermissao(['Adm']);

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT m.idmotor, m.motor_modelo, m.motor_fabricante, m.motor_potencia, m.motor_tensão, m.motor_corrente
            FROM motor m
            WHERE m.idmotor = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $motor = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            echo "Motor não encontrado.";
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
$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sqlDelete = "DELETE FROM motor WHERE idmotor = :id";
    try {
        $deleteStmt = $pdo->prepare($sqlDelete);
        $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $deleteStmt->execute();

        $sucesso = true;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $erro = "Não foi possível excluir o motor, pois ele está associado a uma máquina.";
        } else {
            $erro = "Erro ao excluir Motor: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<head>
    <meta charset="UTF-8">
    <title>Excluir Motor</title>
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

    .error-message {
        color: red;
        font-weight: bold;
    }
    @media (max-width: 598px) {
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
    <?php elseif ($erro): ?>
        <div class="modal" id="errorModal">
            <div class="modal-content">
                <p class="error-message"><?php echo $erro; ?></p>
                <a href="consulta.php" class="back-button">Voltar para a lista de Motor</a>
            </div>
        </div>
    <?php else: ?>
        <div class="modal" id="myModal">
            <div class="modal-content">
                <h2>Excluir Motor</h2>
                <p>Você tem certeza que deseja excluir o Motor
                    <strong><?php echo htmlspecialchars($motor['motor_modelo']); ?></strong>?
                </p>
                <p>Fabricante: <?php echo htmlspecialchars($motor['motor_fabricante']); ?></p>
                <p>Potência: <?php echo htmlspecialchars($motor['motor_potencia']); ?></p>
                <p>Tensão: <?php echo htmlspecialchars($motor['motor_tensão']); ?></p>
                <p>Corrente: <?php echo htmlspecialchars($motor['motor_corrente']); ?></p>

                <form action="" method="post">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($motor['idmotor']); ?>">
                    <input type="submit" class="button" value="Excluir">
                </form>
                <a href="consulta.php" class="cancel-button">Cancelar</a>
            </div>
        </div>
    <?php endif; ?>
</body>

</html>