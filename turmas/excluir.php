    <?php
    include '../conexao.php'; // Verifique se o caminho está correto



    session_start();
    require_once '../verifica_permissao.php';
    verificaPermissao(['Adm']);

    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        $erro = "";
        // Adicionando a cláusula WHERE para filtrar pela turma com o ID fornecido
        $sql = "SELECT t.idturmas, t.turma_nome, t.turma_periodo, t.turma_inicio, t.turma_fim
                FROM turmas t
                WHERE t.idturmas = :id";

        try {
            $stmt = $pdo->prepare($sql);
            // Vincule o parâmetro :id
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $turma = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                echo "Turma não encontrado.";
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
        $sqlDelete = "DELETE FROM turmas WHERE idturmas = :id";
        try {
            $deleteStmt = $pdo->prepare($sqlDelete);
            $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $deleteStmt->execute();

            $sucesso = true;
        }catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $erro = "Não foi possível excluir a turma, pois ela está associada a um curso.";
            } else {
                $erro = "Erro ao excluir turma: " . htmlspecialchars($e->getMessage());
            }
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Excluir Turma</title>
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

        .cancel-button {
            display: block;
            margin-top: 10px;
            text-align: center;
            color: #B30000;
            text-decoration: none;
            font-size: 16px;
        }

        .cancel-button:hover {
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

        @media (max-width: 498px) {
        .modal-content {
            width: 80%; /* Aumenta para quase toda a largura da tela */
    
        }

    }
        /* Responsividade para dispositivos móveis */
    </style>

    <body>
        <?php if ($sucesso): ?>
            <div class="modal" id="successModal">
                <div class="modal-content">
                    <p class="success-message">Turma excluída com sucesso!</p>
                    <a href="consulta.php" class="back-button">Voltar para a lista de Turmas</a>
                </div>
            </div>
        <?php elseif ($erro): ?>
        <div class="modal" id="errorModal">
            <div class="modal-content">
                <p class="error-message"><?php echo $erro; ?></p>
                <a href="consulta.php" class="back-button">Voltar para a lista de Turmas</a>
            </div>
        </div>
        <?php else: ?>
            <div class="modal" id="myModal">
                <div class="modal-content">
                    <h2>Excluir Turma</h2>
                    <p>Você tem certeza que deseja excluir a Turma
                        <strong><?php echo htmlspecialchars($turma['turma_nome']); ?></strong>?</p>
                    <p>Período: <?php echo htmlspecialchars($turma['turma_periodo']); ?></p>
                    <p>Início da Turma: <?php echo htmlspecialchars($turma['turma_inicio']); ?></p>
                    <p>Fim da Turma: <?php echo htmlspecialchars($turma['turma_fim']); ?></p>

                    <form action="" method="post">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($turma['idturmas']); ?>">
                        <input type="submit" class="button" value="Excluir">
                    </form>
                    <a href="consulta.php" class="cancel-button">Cancelar</a>
                </div>
            </div>

        <?php endif; ?>
    </body>

    </html>