<?php
// Definindo o diretório base e incluindo a sidebar
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');

include __DIR__ . '/../sidebar.php';
include '../conexao.php'; // Verifique se o caminho está correto
require_once '../verifica_permissao.php';
verificaPermissao(['Adm']); // Verifica se o usuário tem permissão de administrador

// Verifica se o ID do aluno foi passado via GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta para obter informações do aluno e turma associada
    $sql = "SELECT a.idaluno, a.aluno_nome, a.aluno_matricula, a.turmas_id, t.turma_nome
            FROM aluno a
            LEFT JOIN turmas t ON a.turmas_id = t.idturmas
            WHERE a.idaluno = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $aluno = $stmt->fetch(PDO::FETCH_ASSOC); // Corrigido para "aluno" ao invés de "turmas"
        } else {
            echo "Aluno não encontrado.";
            exit;
        }
    } catch (PDOException $e) {
        echo "Erro: " . htmlspecialchars($e->getMessage());
        exit;
    }
} else {
    echo "ID do aluno não fornecido.";
    exit;
}

// Consulta para obter a lista de todas as turmas
$sqlTurmas = "SELECT idturmas, turma_nome FROM turmas WHERE turmas_status = 'Ativo'";
$turmasStmt = $pdo->prepare($sqlTurmas);
$turmasStmt->execute();
$turmasList = $turmasStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Editar Aluno</title>
</head>
<style>
        @media(max-width: 498px){
            form{
                width: 85%;
            }
        }
    </style>
<body style="display: flex; text-align: center; justify-content: center;">
    <h2>Editar Aluno</h2>
    <!-- Exibe a mensagem de erro, caso exista -->
    <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="modal <?php echo $_SESSION['mensagem_tipo']; ?>">
            <?php echo htmlspecialchars($_SESSION['mensagem']); ?>
        </div>
        <?php unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']); ?> <!-- Limpa a mensagem da sessão após exibir -->
    <?php endif; ?>

    <form action="atualizar.php" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($aluno['idaluno']); ?>">

        <label for="aluno_nome">Nome:</label>
        <input type="text" id="aluno_nome" name="aluno_nome" value="<?php echo htmlspecialchars($aluno['aluno_nome']); ?>" required>

        <label for="aluno_matricula">Matrícula:</label>
        <input type="text" 
               id="aluno_matricula" 
               name="aluno_matricula" 
               inputmode="numeric" 
               pattern="[0-9]*" 
               maxlength="8" 
               value="<?php echo htmlspecialchars($aluno['aluno_matricula']); ?>" 
               required 
               oninput="this.value = this.value.replace(/[^0-9]/g, '');">

        <label for="turmas_id">Turma:</label>
        <select id="turmas_id" name="turmas_id" required>
            <?php foreach ($turmasList as $turma): ?>
                <option value="<?php echo htmlspecialchars($turma['idturmas']); ?>" <?php echo $aluno['turmas_id'] == $turma['idturmas'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($turma['turma_nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="Atualizar">
    </form>
    <a href="consulta.php" class="lista_consulta">Ir para a lista de Alunos</a>
</body>
</html>