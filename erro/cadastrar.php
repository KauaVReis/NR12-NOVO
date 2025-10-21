<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');

// Inclui dependências
include __DIR__ . '/../sidebar.php';
include __DIR__ . '/../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador', 'Professor']);

$modalMessage = ""; // Variável para armazenar a mensagem do modal
$modalTitle = "";   // Variável para armazenar o título do modal

// Verifica se o ID do colaborador está na sessão
if (isset($_SESSION['user_id'])) {
    $colaborador = $_SESSION['user_id'];
} else {
    $modalMessage = "Usuário não está logado.";
    $modalTitle = "Erro!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $descErro = $_POST['desc_erro'];
        $idColaborador = $_POST['id_colaborador'];
        $dataSolicitacao = date("Y-m-d H:i:s"); // Data no formato correto para MySQL

        // Consulta SQL com os nomes corretos dos campos
        $sql = "INSERT INTO solicitacao_erro (id_colaborador, desc_erro, data_solicitacao) 
                VALUES (:idColaborador, :descErro, :dataSolicitacao)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idColaborador', $idColaborador);
        $stmt->bindParam(':descErro', $descErro);
        $stmt->bindParam(':dataSolicitacao', $dataSolicitacao);

        if ($stmt->execute()) {
            $modalMessage = "Erro reportado com sucesso!";
            $modalTitle = "Sucesso!";
        } else {
            $modalMessage = "Erro ao reportar o problema.";
            $modalTitle = "Erro!";
        }
    } catch (PDOException $e) {
        $modalMessage = "Erro ao conectar ao banco: " . $e->getMessage();
        $modalTitle = "Erro!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Erro</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <style>
        /* Estilização */
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            height: 100vh;
            margin: 0;
        }
        textarea {
            width: 90%;
            padding: 10px;
            margin-top: 8px;
            border: 2px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            resize: none;
        }
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            color: black;
        }
        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        form{
            padding: 25px;
            width: 500px;
        }
        .close-modal {
            background: #e21616;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }
        .close-modal:hover {
            background-color: #b01313;
        }
        @media (max-width: 768px) {
            h2 {
                margin-top: 0;
            }
            form {
                max-width: 335px;
            }
        }
    </style>
</head>

<body>
    <div id="successModal" class="modal">
        <div class="modal-content">
            <h2 id="modal-title">Mensagem</h2>
            <p id="modal-message"></p>
            <button class="close-modal" onclick="closeModal()">Fechar</button>
        </div>
    </div>

    <div class="container-cadastro-funcionario">
        <h2>Suporte</h2>
        <form action="" method="post">
            <label for="nome">Colaborador:</label>
            <?php
            $sql = "SELECT idcolaborador, colaborador_nome FROM colaborador WHERE idcolaborador = :colaborador";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':colaborador', $colaborador);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo "<input type='text' name='colaborador_nome' readonly value='" . htmlspecialchars($row['colaborador_nome']) . "'/>";
                    echo "<input type='hidden' name='id_colaborador' value='" . htmlspecialchars($row['idcolaborador']) . "'/>";
                } else {
                    echo "<input type='text' name='colaborador_nome' readonly value='Colaborador não encontrado' />";
                }
            } catch (PDOException $e) {
                echo "<input type='text' name='colaborador_nome' readonly value='Erro ao buscar colaborador' />";
            }
            ?>

            <label for="desc_erro">Descrição do erro:</label>
            <textarea name="desc_erro" id="" required></textarea>
            <input type="submit" value="Cadastrar">
        </form>
    </div>

    <script>
        function openModal(title, message) {
            document.getElementById("modal-title").textContent = title;
            document.getElementById("modal-message").textContent = message;
            document.getElementById("successModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("successModal").style.display = "none";
        }

        window.onclick = function (event) {
            const modal = document.getElementById("successModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };

        <?php if (!empty($modalMessage)): ?>
        openModal("<?php echo addslashes($modalTitle); ?>", "<?php echo addslashes($modalMessage); ?>");
        <?php endif; ?>
    </script>
</body>

</html>
