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

$unidade_nome = '';
$unidade_cidade = '';
$unidade_estado = '';
$unidade_numero = '';
$idunidade = null; // Inicializa $idunidade
$sucesso = false; // Variável para controlar o modal

// Verifica se o ID da unidade foi enviado pela URL (GET)
if (isset($_GET['id'])) {
    $idunidade = $_GET['id']; // Corrigido: usa $_GET['id']

    try {
        $sql = "SELECT * FROM unidade WHERE idunidade = :idunidade";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idunidade', $idunidade);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $unidade_nome = $row['unidade_nome'];
            $unidade_cidade = $row['unidade_cidade'];
            $unidade_estado = $row['unidade_estado'];
            $unidade_numero = $row['unidade_numero'];
        } else {
            echo "Unidade não encontrada.";
            exit();
        }

    } catch (PDOException $e) {
        die("Erro ao buscar dados: " . $e->getMessage());
    }
}

// Processa a atualização da unidade
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    // Coletando os dados do formulário
    $unidade_nome = $_POST['unidade_nome'];
    $unidade_cidade = $_POST['unidade_cidade'];
    $unidade_estado = $_POST['unidade_estado'];
    $unidade_numero = $_POST['unidade_numero'];

    // Atualiza os dados da unidade na tabela
    $sql = "UPDATE unidade SET 
                unidade_nome = :unidade_nome, 
                unidade_cidade = :unidade_cidade, 
                unidade_estado = :unidade_estado, 
                unidade_numero = :unidade_numero 
            WHERE idunidade = :idunidade";

    $stmt = $pdo->prepare($sql);

    // Bind dos parâmetros
    $stmt->bindParam(':unidade_nome', $unidade_nome);
    $stmt->bindParam(':unidade_cidade', $unidade_cidade);
    $stmt->bindParam(':unidade_estado', $unidade_estado);
    $stmt->bindParam(':unidade_numero', $unidade_numero);
    $stmt->bindParam(':idunidade', $idunidade);

    // Executando a atualização
    if ($stmt->execute()) {
        $sucesso = true; // Marca o sucesso para exibir o modal
    } else {
        echo "Erro ao atualizar a unidade.";
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
    <title>Editar Unidade</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>

<style>
    body {
        display: flex;
        text-align: center;
        justify-content: center;
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

<body>
    <h2>Editar Unidade</h2>
    <form method="post">
        <input type="hidden" name="idunidade" value="<?= htmlspecialchars($idunidade) ?>">

        <label>Nome da Unidade:</label>
        <input type="text" name="unidade_nome" value="<?php echo htmlspecialchars($unidade_nome); ?>" required>

        <label>Cidade:</label>
        <input type="text" name="unidade_cidade" value="<?php echo htmlspecialchars($unidade_cidade); ?>" required>

        <label>Estado:</label>
        <input type="text" name="unidade_estado" value="<?php echo htmlspecialchars($unidade_estado); ?>" required>

        <label>Número:</label>
        <input type="text" name="unidade_numero" value="<?php echo htmlspecialchars($unidade_numero); ?>" required>

        <input type="submit" name="update" value="Atualizar"></input>
    </form>

    <!-- Modal de Sucesso -->
    <?php if ($sucesso): ?>
        <div class="modal fade show" id="sucessoModal" tabindex="-1" aria-labelledby="sucessoModalLabel" aria-hidden="true" style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="background-color: #f8f9fa; color: #000;">
                        <h5 class="modal-title" id="sucessoModalLabel">Sucesso!</h5>
                    </div>
                    <div class="modal-body" style="background-color: #f8f9fa; color: #000;">
                        Unidade atualizada com sucesso!
                    </div>
                    <div class="modal-footer" style="background-color: #f8f9fa;">
                        <a href="consulta.php" class="btn btn-success">Ok</a>
                    </div>
                </div>
            </div>
        </div>
        <script>
            const modal = new bootstrap.Modal(document.getElementById('sucessoModal'));
            modal.show();
        </script>
    <?php endif; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>
