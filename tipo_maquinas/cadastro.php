<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);

// Adiciona uma barra no final se não houver
$base_dir = rtrim($base_dir, '/') . '/';

// Corrige a URL para sempre começar do diretório raiz do projeto
define('BASE_URL', '../../nr12/');
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/nr12/sidebar.php'; ?>

<?php
// Incluindo a conexão com o banco de dados
include '../conexao.php';

$modalMessage = ""; // Variável para armazenar a mensagem do modal
$modalTitle = "";   // Variável para armazenar o título do modal

try {
    // Definindo o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Processar o formulário se o método POST for usado
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nome = $_POST['nome_tipodemaquina'];
        $arquivoPath = null; // Inicializa o caminho do arquivo Excel

        // Verifica se o arquivo Excel foi enviado e processa o upload
        if (isset($_FILES['excel_apoio']) && $_FILES['excel_apoio']['error'] == UPLOAD_ERR_OK) {
            $excelFile = $_FILES['excel_apoio'];
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/nr12/uploads/'; // Define o diretório de upload
            $arquivoPath = $uploadDir . basename($excelFile['name']);

            // Verifica se o arquivo tem extensão .xlsx ou .xls
            $extensao = strtolower(pathinfo($excelFile['name'], PATHINFO_EXTENSION));
            if ($extensao === 'xlsx' || $extensao === 'xls') {
                // Move o arquivo para o diretório de upload
                if (move_uploaded_file($excelFile['tmp_name'], $arquivoPath)) {
                    $arquivoPath = '/nr12/uploads/' . basename($excelFile['name']); // Caminho relativo para salvar no banco
                } else {
                    $modalMessage = "Erro ao fazer upload do arquivo Excel.";
                }
            } else {
                $modalMessage = "Por favor, envie um arquivo Excel válido (.xlsx ou .xls).";
            }
        }

        // Insere os dados do tipo de máquina, incluindo o caminho do arquivo Excel
        if (!$modalMessage) { // Só insere se não houver erro anterior
            $sql = "INSERT INTO tipomaquina (tipomaquina_nome, tipomaquina_arquivo) VALUES (:nome, :arquivo)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':arquivo', $arquivoPath);

            if ($stmt->execute()) {
                $modalMessage = "Registro realizado com sucesso!";
                $modalTitle = "Sucesso!";
            } else {
                $modalMessage = "Erro ao cadastrar o tipo de máquina.";
                $modalTitle = "Erro!";
            }
        }
    }
} catch (PDOException $e) {
    $modalMessage = "Falha na conexão: " . $e->getMessage();
    $modalTitle = "Erro!";
}

// Fechar a conexão (opcional)
$pdo = null;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Tipo de Máquina</title>
    <style>
        /* Estilos do botão e do modal */
        .botao-enviar-tipoMaquina {
            background-color: #e21616;
            color: #ffffff;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
            transition: background-color 0.3s, box-shadow 0.3s, transform 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            margin-bottom: 12px;
        }

        .botao-enviar-tipoMaquina:hover {
            background-color: #fd2020;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-3px);
        }

        .container-cadastro-tipo-maquina {
            padding-top: 250px;
            text-align: center;
        }

        @media (max-width: 540px) {
            #TipoMaquina {
                width: 100%;
                width: 300px;
                max-width: 500px;
            }

            .container-cadastro-tipo-maquina {
                padding-top: 70px;
                text-align: center;
            }
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

        .modal-content h2 {
            margin-top: 0;
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

<div class="container-cadastro-tipo-maquina">
    <h1>Cadastro de Tipo de Máquina</h1>
    <form id="TipoMaquina" action="" method="post" enctype="multipart/form-data">
        <label>Nome:</label>
        <input type="text" name="nome_tipodemaquina" required>

        <label>Arquivo Excel de Apoio:</label>
        <input type="file" name="excel_apoio" accept=".xlsx, .xls">

        <button type="submit" class="botao-enviar-tipoMaquina">Enviar</button>
    </form>
</div>

<script>
    // Função para abrir o modal
    function openModal(title, message) {
        document.getElementById("modal-title").textContent = title; // Atualiza o título
        document.getElementById("modal-message").textContent = message; // Atualiza a mensagem
        document.getElementById("successModal").style.display = "flex"; // Mostra o modal
    }

    // Função para fechar o modal
    function closeModal() {
        document.getElementById("successModal").style.display = "none";
    }

    // Fecha o modal se o usuário clicar fora do conteúdo
    window.onclick = function (event) {
        const modal = document.getElementById("successModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };

    // Exibe o modal com a mensagem caso exista uma mensagem do servidor
    <?php if (!empty($modalMessage)): ?>
        openModal("<?php echo addslashes($modalTitle); ?>", "<?php echo addslashes($modalMessage); ?>");
        setTimeout(() => {
            window.location.href = 'cadastro.php';
        }, 3000); // Tempo de exibição ajustado para 3 segundos
    <?php endif; ?>
</script>
</body>

</html>
