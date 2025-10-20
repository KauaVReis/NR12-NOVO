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
$motor_fabricante = '';
$motor_modelo = '';
$motor_potencia = '';
$motor_tensao = '';
$motor_corrente = '';
$modalSucesso = false; // Variável para controlar o modal

// Verifica se o ID do motor foi enviado via POST para consulta e exibição dos dados
if (isset($_GET['id'])) {
    $idmotor = $_GET['id'];

    // Consulta para obter os dados do motor
    $sql = "SELECT * FROM motor WHERE idmotor = :idmotor";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idmotor', $idmotor);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $motor_fabricante = $row['motor_fabricante'];
        $motor_modelo = $row['motor_modelo'];
        $motor_potencia = $row['motor_potencia'];
        $motor_tensao = $row['motor_tensão'];
        $motor_corrente = $row['motor_corrente'];
    } else {
        echo "Motor não encontrado.";
        exit();
    }
}

// Processa a atualização do motor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {  // Verifique se o formulário foi enviado
    // Coletando os dados do formulário
    $motor_fabricante = $_POST['motor_fabricante'];
    $motor_modelo = $_POST['motor_modelo'];
    $motor_potencia = $_POST['motor_potencia'];
    $motor_tensao = $_POST['motor_tensão'];
    $motor_corrente = $_POST['motor_corrente'];
    $idmotor = $_POST['idmotor'];  // Captura o idmotor via POST também

    // Atualiza os dados do motor na tabela
    $sql = "UPDATE motor SET 
            motor_fabricante = :motor_fabricante, 
            motor_modelo = :motor_modelo, 
            motor_potencia = :motor_potencia, 
            motor_tensão = :motor_tensao,
            motor_corrente = :motor_corrente
        WHERE idmotor = :idmotor";

    $stmt = $pdo->prepare($sql);

    // Bind dos parâmetros
    $stmt->bindParam(':motor_fabricante', $motor_fabricante);
    $stmt->bindParam(':motor_modelo', $motor_modelo);
    $stmt->bindParam(':motor_potencia', $motor_potencia);
    $stmt->bindParam(':motor_tensao', $motor_tensao);
    $stmt->bindParam(':motor_corrente', $motor_corrente);
    $stmt->bindParam(':idmotor', $idmotor);

    // Executando a atualização
    if ($stmt->execute()) {
        $modalSucesso = true; // Exibe o modal de sucesso
    } else {
        echo "Erro ao atualizar o motor.";
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
    <title>Editar Motor</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .voltar {
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

        .voltar:hover {
            background-color: #fd2020;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-3px);
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .modal-content h2 {
            margin-bottom: 16px;
        }

        .modal-content button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .modal-content button:hover {
            background-color: #45a049;
        }

        @media (max-width: 598px) {
            form {
                width: 85%;
            }
            h2 {
                font-size: 25px;
            }
            body {
                margin-top: 45px;
            }
        }
    </style>
</head>
<body>
    <h2>Editar Motor</h2>
    <form method="post">
        <input type="hidden" name="idmotor" value="<?php echo htmlspecialchars($idmotor); ?>">

        <label>Fabricante do Motor:</label>
        <input type="text" name="motor_fabricante" value="<?php echo htmlspecialchars($motor_fabricante); ?>" required>

        <label>Modelo do Motor:</label>
        <input type="text" name="motor_modelo" value="<?php echo htmlspecialchars($motor_modelo); ?>" required>

        <label>Potência do Motor:</label>
        <input type="text" name="motor_potencia" value="<?php echo htmlspecialchars($motor_potencia); ?>" required>

        <label>Tensão do Motor:</label>
        <input type="text" name="motor_tensão" value="<?php echo htmlspecialchars($motor_tensao); ?>" required>

        <label>Corrente do Motor:</label>
        <input type="text" name="motor_corrente" value="<?php echo htmlspecialchars($motor_corrente); ?>" required>

        <input type="submit" name="update" value="Atualizar">
    </form>

    <button class="voltar" onclick="window.location.href='consulta.php'">Voltar Para Consulta</button>

    <?php if ($modalSucesso): ?>
        <div class="modal">
            <div class="modal-content">
                <h2>Motor atualizado com sucesso!</h2>
                <button onclick="window.location.href='consulta.php'">Fechar</button>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
