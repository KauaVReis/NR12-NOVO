<?php
session_start();
require_once '../verifica_permissao.php';
include '../conexao.php';

$sucesso = null;
$erro = null;

if (isset($_GET['id'])) {
    $idmanutencao = $_GET['id'];

    // Tenta recuperar os dados da manutenção
    $stmt = $pdo->prepare("SELECT * FROM manutencao WHERE idmanutencao = :idmanutencao");
    $stmt->bindValue(":idmanutencao", $idmanutencao);
    $stmt->execute();

    $manutencao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$manutencao) {
        $_SESSION['message'] = 'Manutenção não encontrada!';
        header('Location: tabela.php');
        exit();
    }

    // Atribuindo os dados da manutenção
    $descricao = $manutencao['manutencao_descricao'];
    $data = $manutencao['manutencao_data'];
    $maquina_id = $manutencao['maquina_id']; // A chave estrangeira correta para buscar na tabela 'maquina'

    // Consulta para buscar o NI da máquina
    $stmt_maquina = $pdo->prepare("SELECT maquina_ni FROM maquina WHERE idmaquina = :idmaquina");
    $stmt_maquina->bindValue(":idmaquina", $maquina_id);
    $stmt_maquina->execute();
    $maquina_data = $stmt_maquina->fetch(PDO::FETCH_ASSOC);

    // Verifica se encontrou o NI da máquina
    if ($maquina_data) {
        $maquina_ni = $maquina_data['maquina_ni'];
    } else {
        $maquina_ni = 'Não encontrado';
    }

    $colaborador = $manutencao['colaborador_id'];
    $estado = $manutencao['manutencao_estado'];
    $data_realizada = $manutencao['manutencao_realizada'];
    $status = $manutencao['manutencao_status'];
    $tipo_manutencao = $manutencao['tipo_manutencao'];

    // Lógica de exclusão
    if (isset($_POST['idmanutencao']) && $_POST['idmanutencao'] == $idmanutencao) {
        try {
            $stmt = $pdo->prepare("DELETE FROM manutencao WHERE idmanutencao = :idmanutencao");
            $stmt->bindValue(":idmanutencao", $idmanutencao);
            $stmt->execute();
    
            $sucesso = "Manutenção excluída com sucesso!";
            header("Location: tabela.php?sucesso=" . urlencode($sucesso));
            exit();
    
        } catch (Exception $e) {
            $erro = "Erro ao excluir a manutenção: " . $e->getMessage();
        }
    }
} else {
    $_SESSION['message'] = 'ID de manutenção não fornecido!';
    header('Location: tabela.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Manutenção</title>
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

        .details {
            text-align: left;
            margin-top: 20px;
        }

        .details p {
            margin: 5px 0;
        }
        @media (max-width: 498px) {
        .modal-content {
            width: 80%; /* Aumenta para quase toda a largura da tela */
    
        }
    }
    </style>
</head>
<body>
    <?php if ($sucesso): ?>
        <div class="modal" id="successModal">
            <div class="modal-content">
                <p class="success-message"><?php echo $sucesso; ?></p>
                <a href="tabela.php" class="back-button">Voltar para a lista de Manutenções</a>
            </div>
        </div>
    <?php elseif ($erro): ?>
        <div class="modal" id="errorModal">
            <div class="modal-content">
                <p class="error-message"><?php echo $erro; ?></p>
                <a href="tabela.php" class="back-button">Voltar para a lista de Manutenções</a>
            </div>
        </div>
    <?php else: ?>
        <div class="modal" id="myModal">
            <div class="modal-content">
                <h2>Excluir Manutenção</h2>
                <p>Você tem certeza que deseja excluir a manutenção realizada na máquina com o NI
                    <strong><?php echo htmlspecialchars($maquina_ni); ?></strong>?<br>
                    <p>Esta ação irá remover permanentemente os dados dessa manutenção, incluindo sua descrição, tipo e
                        status.</p>
                </p>

                <div class="details">
                    <p><strong>Descrição:</strong> <?= htmlspecialchars($descricao) ?></p>
                    <p><strong>Data da Manutenção:</strong> <?= htmlspecialchars($data) ?></p>
                    <p><strong>Máquina (NI):</strong> <?= htmlspecialchars($maquina_ni) ?></p> <!-- Exibe o NI correto -->
                    <p><strong>Colaborador:</strong> <?= htmlspecialchars($colaborador) ?></p>
                    <p><strong>Estado:</strong> <?= htmlspecialchars($estado) ?></p>
                    <p><strong>Tipo de Manutenção:</strong> <?= htmlspecialchars($tipo_manutencao) ?></p>
                    <p><strong>Data Realizada:</strong> <?= htmlspecialchars($data_realizada) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($status) ?></p>

                    <!-- Confirmação de exclusão -->
                    <form action="" method="post">
                        <input type="hidden" name="idmanutencao" value="<?php echo htmlspecialchars($manutencao['idmanutencao']); ?>">
                        <input type="submit" class="button" value="Excluir">
                    </form>
                    <a href="tabela.php" class="cancel-button">Cancelar</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
