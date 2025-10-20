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

// Inicializa a variável
$tipomaquina_nome = '';

// Verifica se o ID do tipo de máquina foi enviado
if (isset($_POST['idtipomaquina'])) {
    $idtipomaquina = $_POST['idtipomaquina'];

    // Consulta para obter os dados do tipo de máquina
    $sql = "SELECT tipomaquina_nome FROM tipomaquina WHERE idtipomaquina = :idtipomaquina";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idtipomaquina', $idtipomaquina);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $tipomaquina_nome = $row['tipomaquina_nome'];
    } else {
        echo "Tipo de máquina não encontrado.";
        exit();
    }
}

// Processa a atualização do tipo de máquina
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $tipomaquina_nome = $_POST['tipomaquina_nome'];

    $sql = "UPDATE tipomaquina SET tipomaquina_nome = :tipomaquina_nome WHERE idtipomaquina = :idtipomaquina";
    $stmt = $pdo->prepare($sql);

    // Bind dos parâmetros
    $stmt->bindParam(':tipomaquina_nome', $tipomaquina_nome);
    $stmt->bindParam(':idtipomaquina', $idtipomaquina);

    // Executa a consulta para atualizar os dados
    if ($stmt->execute()) {
        echo "<script>alert('Tipo de máquina atualizado com sucesso!'); window.location.href='consulta.php';</script>";
    } else {
        echo "Erro ao atualizar o tipo de máquina.";
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
    <title>Editar Tipo de Máquina</title>
</head>
<body>
    <h2>Editar Tipo de Máquina</h2>
    <form method="post">
        <input type="hidden" name="idtipomaquina" value="<?php echo htmlspecialchars($idtipomaquina); ?>">
        <label>Nome da Máquina:</label>
        <input required type="text" name="tipomaquina_nome" value="<?php echo htmlspecialchars($tipomaquina_nome); ?>"><br>
       
        
        <button type="submit" name="update">Atualizar</button>
    </form>
</body>
</html>
