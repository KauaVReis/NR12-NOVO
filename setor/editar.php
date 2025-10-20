<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);

// Adiciona uma barra no final se não houver
$base_dir = rtrim($base_dir, '/') . '/';

// Corrige a URL para sempre começar do diretório raiz do projeto
// define('BASE_URL', '../../nr12/');
?>
<?php include __DIR__ . '/../sidebar.php'; ?>

<?php
include '../conexao.php';

require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador']);

try {
    // Definindo o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Falha na conexão: " . $e->getMessage());
}

// Inicializa variáveis
$setor_nome = '';
$unidade_id = '';
$success_message = false; // Variável para exibir o modal

// Verifica se o ID do setor foi enviado
if (isset($_GET['id'])) {
    $idsetor = $_GET['id'];

    // Consulta para obter os dados do setor
    $sql = "SELECT * FROM setor WHERE idsetor = :idsetor ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idsetor', $idsetor);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $setor_nome = $row['setor_nome'];
        $unidade_id = $row['unidade_id'];
    } else {
        echo "Setor não encontrado.";
        exit();
    }
}

// Consulta para obter as unidades disponíveis
$sql_unidades = "SELECT idunidade, unidade_nome FROM unidade WHERE unidade_status = 'Ativo'";
$stmt_unidades = $pdo->query($sql_unidades);
$unidades = $stmt_unidades->fetchAll(PDO::FETCH_ASSOC);

// Processa a atualização do setor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    // Coletando os dados do formulário
    $setor_nome = $_POST['setor_nome'];
    $unidade_id = $_POST['unidade_id'];

    // Atualiza os dados do setor na tabela
    $sql = "UPDATE setor SET 
                setor_nome = :setor_nome, 
                unidade_id = :unidade_id 
            WHERE idsetor = :idsetor";

    $stmt = $pdo->prepare($sql);

    // Bind dos parâmetros
    $stmt->bindParam(':setor_nome', $setor_nome);
    $stmt->bindParam(':unidade_id', $unidade_id);
    $stmt->bindParam(':idsetor', $idsetor);

    // Executando a atualização
    if ($stmt->execute()) {
        $success_message = true; // Define que o modal de sucesso deve ser exibido
    } else {
        echo "Erro ao atualizar o setor.";
    }
}

// Fechar a conexão (opcional com PDO)
$pdo = null;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Setor</title>
    <style>
        form {
            padding: 25px;
            width: 500px;
        }

        body {
            display: flex;
            text-align: center;
            justify-content: center;
        }

        /* Estilo para o link de retorno */
        .back-link {
            background-color: #e21616;
            text-decoration: none;
            color: #ffffff;
            padding: 12px 24px;
            margin: 12px 0;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
            transition: background-color 0.3s, box-shadow 0.3s, transform 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .back-link:hover {
            color: #800000;
        }

        /* Botão de atualização com estilo */
        .edit-button {
            background-color: #e21616;
            color: #ffffff;
            padding: 12px 24px;
            margin: 12px 0;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-transform: uppercase;
            transition: background-color 0.3s, box-shadow 0.3s, transform 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .edit-button:hover {
            background-color: #800000;
        }

        /* Modal de sucesso */
        .modal-success {
            position: fixed;
            top: 90%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #28a745;
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            z-index: 1000;
        }

        .modal-success.hidden {
            display: none;
        }

        @media (max-width: 600px) {
            form {
                width: 85%;
            }

            h2 {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>
    <?php if ($success_message): ?>
        <div id="modal-success" class="modal-success">
            Setor atualizado com sucesso!
        </div>
        <script>
            // Remove o modal após 3 segundos
            setTimeout(() => {
                document.getElementById('modal-success').classList.add('hidden');
            }, 3000);
        </script>
    <?php endif; ?>

    <h2>Editar Setor</h2>

    <!-- Formulário de edição -->
    <form method="post">
        <input type="hidden" name="idsetor" value="<?php echo htmlspecialchars($idsetor); ?>">

        <label>Nome do Setor:</label>
        <input type="text" name="setor_nome" value="<?php echo htmlspecialchars($setor_nome); ?>" required>

        <label>Unidade:</label>
        <select name="unidade_id" required>
            <?php foreach ($unidades as $unidade): ?>
                <option value="<?php echo $unidade['idunidade']; ?>" <?php echo ($unidade['idunidade'] == $unidade_id) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($unidade['unidade_nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Botão de atualização -->
        <button type="submit" name="update" class="edit-button">Atualizar</button>
    </form>

    <!-- Link para voltar -->
    <a href="consulta.php" class="back-link">Voltar para Consulta</a>
</body>

</html>
