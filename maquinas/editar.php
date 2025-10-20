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
// Incluindo a conexão com o banco de dados
include '../conexao.php';

require_once '../verifica_permissao.php';
verificaPermissao(['Adm']);

try {
    // Definindo o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Falha na conexão: " . $e->getMessage());
}

// Verifica se o ID foi passado via GET
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Consultar a máquina correspondente ao ID
    $sql = "SELECT * FROM maquina WHERE idmaquina = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $maquina = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se a máquina não for encontrada, redireciona ou exibe um erro
    if (!$maquina) {
        die("Máquina não encontrada.");
    }
}

// Consulta para obter os motores ativos
$motores_sql = "SELECT idmotor, motor_fabricante, motor_modelo FROM motor WHERE motor_status = 'Ativo'";
$motores_stmt = $pdo->prepare($motores_sql);
$motores_stmt->execute();
$motores = $motores_stmt->fetchAll(PDO::FETCH_ASSOC);

// Atualizar a máquina se o formulário foi enviado
// Atualizar a máquina se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $motor_id = $_POST['motor_id'];
        $maquina_ni = $_POST['maquina_ni'];
        $tipomaquina_id = $_POST['tipomaquina_id'];
        $setor_id = $_POST['setor_id'];
        $maquina_peso = $_POST['maquina_peso'];
        $maquina_fabricante = $_POST['maquina_fabricante'];
        $maquina_modelo = $_POST['maquina_modelo'];
        $maquina_ano = $_POST['maquina_ano'];
        $maquina_capacidade = $_POST['maquina_capacidade'];
        $maquina_status = $_POST['maquina_status'];

        // Atualiza os dados da máquina no banco de dados
        $update_sql = "UPDATE maquina SET 
            motor_id = :motor_id,
            maquina_ni = :maquina_ni,
            tipomaquina_id = :tipomaquina_id,
            setor_id = :setor_id,
            maquina_peso = :maquina_peso,
            maquina_fabricante = :maquina_fabricante,
            maquina_modelo = :maquina_modelo,
            maquina_ano = :maquina_ano,
            maquina_capacidade = :maquina_capacidade,
            maquina_status = :maquina_status
            WHERE idmaquina = :id";

        $update_stmt = $pdo->prepare($update_sql);

        // Vincular parâmetros
        $update_stmt->bindParam(':motor_id', $motor_id, PDO::PARAM_INT);
        $update_stmt->bindParam(':maquina_ni', $maquina_ni);
        $update_stmt->bindParam(':tipomaquina_id', $tipomaquina_id);
        $update_stmt->bindParam(':setor_id', $setor_id);
        $update_stmt->bindParam(':maquina_peso', $maquina_peso);
        $update_stmt->bindParam(':maquina_fabricante', $maquina_fabricante);
        $update_stmt->bindParam(':maquina_modelo', $maquina_modelo);
        $update_stmt->bindParam(':maquina_ano', $maquina_ano);
        $update_stmt->bindParam(':maquina_capacidade', $maquina_capacidade);
        $update_stmt->bindParam(':maquina_status', $maquina_status);
        $update_stmt->bindParam(':id', $id, PDO::PARAM_INT);

        // Executar a atualização
        if ($update_stmt->execute()) {
            echo "<script>
                    alert('Máquina atualizada com sucesso!');
                    window.location.href = 'consulta.php';
                  </script>";
            exit;
        } else {
            echo "<script>alert('Erro ao atualizar a máquina.');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Erro de atualização: " . $e->getMessage() . "');</script>";
    }
}


// Consulta para obter os tipos de máquinas
$tipos_maquinas_sql = "SELECT idtipomaquina, tipomaquina_nome FROM tipomaquina WHERE tipomaquina_status = 'Ativo'"; // Ajuste o nome da tabela e coluna conforme seu banco de dados
$tipos_maquinas_stmt = $pdo->prepare($tipos_maquinas_sql);
$tipos_maquinas_stmt->execute();
$tipos_maquinas = $tipos_maquinas_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Máquina</title>
</head>

<body>
    <h1>Editar Máquina</h1>
    <form method="POST">
        <label for="maquina_ni">Máquina NI:</label>
        <input type="text" id="maquina_ni" name="maquina_ni" value="<?= htmlspecialchars($maquina['maquina_ni']) ?>"
            required>
        <br>

        <label for="tipomaquina_id">Tipo de Máquina:</label>
        <select id="tipomaquina_id" name="tipomaquina_id" required>
            <?php foreach ($tipos_maquinas as $tipo): ?>
                <option value="<?= $tipo['idtipomaquina'] ?>" <?= $tipo['idtipomaquina'] == $maquina['tipomaquina_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tipo['tipomaquina_nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>

        <label for="setor_id">Setor:</label>
        <input type="number" id="setor_id" name="setor_id" value="<?= htmlspecialchars($maquina['setor_id']) ?>"
            required>
        <br>

        <label for="maquina_peso">Peso:</label>
        <input type="text" id="maquina_peso" name="maquina_peso"
            value="<?= htmlspecialchars($maquina['maquina_peso']) ?>" required>
        <br>

        <label for="maquina_fabricante">Fabricante:</label>
        <input type="text" id="maquina_fabricante" name="maquina_fabricante"
            value="<?= htmlspecialchars($maquina['maquina_fabricante']) ?>" required>
        <br>

        <label for="maquina_modelo">Modelo:</label>
        <input type="text" id="maquina_modelo" name="maquina_modelo"
            value="<?= htmlspecialchars($maquina['maquina_modelo']) ?>" required>
        <br>

        <label for="maquina_ano">Ano:</label>
        <input type="number" id="maquina_ano" name="maquina_ano"
            value="<?= htmlspecialchars($maquina['maquina_ano']) ?>" required>
        <br>

        <label for="maquina_capacidade">Capacidade:</label>
        <input type="text" id="maquina_capacidade" name="maquina_capacidade"
            value="<?= htmlspecialchars($maquina['maquina_capacidade']) ?>" required>
        <br>

        <label for="maquina_status">Status:</label>
        <select id="maquina_status" name="maquina_status" required>
            <option value="Ativo" <?= $maquina['maquina_status'] == 'Ativo' ? 'selected' : '' ?>>Ativo</option>
            <option value="Inativo" <?= $maquina['maquina_status'] == 'Inativo' ? 'selected' : '' ?>>Inativo</option>
        </select>
        <br>
        <label for="motor_id">Motor:</label>
        <select id="motor_id" name="motor_id" required>
            <option value="">Selecione</option>
            <?php foreach ($motores as $motor): ?>
                <option value="<?= $motor['idmotor'] ?>" <?= $motor['idmotor'] == $maquina['motor_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($motor['motor_fabricante'] . ' - ' . $motor['motor_modelo']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="Atualizar">
    </form>
    <a class="input" href="consulta.php">Voltar para Consulta</a>
</body>

<style>
    body {
        font-family: sans-serif;
        margin: 20px;
        /* Adiciona uma margem ao redor do conteúdo */
        background-color: #cfcfcf;
        /* Cor de fundo clara */
    }

    h1 {
        text-align: center;
        color: #333;
        /* Cor de texto escura */
        padding: 10px;
    }

    form {
        background-color: white;
        padding: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        /* Adiciona uma sombra sutil */
        max-width: 600px;
        /* Define uma largura máxima para o formulário */
        margin: 0 auto;
        /* Centraliza o formulário na página */
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }

    input[type="text"],
    input[type="number"],
    select {
        width: 80%;
        /* Faz os campos de input e select ocuparem 80% da largura disponível */
        padding: 10px;
        /* Ajusta o padding dos inputs */
        border: 1px solid #ccc;
        /* Borda sutil e uniforme para todos os campos */
        border-radius: 4px;
        /* Bordas arredondadas */
        box-sizing: border-box;
        /* Inclui o padding e border no cálculo da largura */
        margin-bottom: 5px;
        /* Espaço entre os campos */
        font-size: 14px;
        /* Define o tamanho da fonte para todos os campos */
    }

    input[type="submit"],
    .input {
        text-decoration: none;
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

    input[type="submit"]:hover,
    .input:hover {
        background-color: #d32f26;
        /* Vermelho mais escuro ao passar o mouse */
    }
    @media (max-width: 590px) {
            form{
                width: 95%;
            }
        }
</style>

</html>