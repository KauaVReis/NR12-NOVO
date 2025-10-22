<?php
ob_start();

// Limpa mensagens residuais ao abrir a página de edição
unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']);
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');

include __DIR__ . '/../sidebar.php';

include '../conexao.php';

require_once '../verifica_permissao.php';
verificaPermissao(['Adm']);

if (isset($_GET['id'])) {
    $idCurso = $_GET['id'];

    try {
        $sql = "SELECT curso_nome, curso_status FROM curso WHERE idcurso = :idCurso";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idCurso', $idCurso);
        $stmt->execute();
        $curso = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$curso) {
            $_SESSION['mensagem_erro'] = "Curso não encontrado.";
            header("Location: consultaCurso.php");
            exit();
        }

        $curso_nome = $curso['curso_nome'];
        $curso_status = $curso['curso_status'];
    } catch (PDOException $e) {
        $_SESSION['mensagem_erro'] = "Erro ao buscar curso: " . $e->getMessage();
        header("Location: consultaCurso.php");
        exit();
    }
} else {
    $_SESSION['mensagem_erro'] = "ID do curso não especificado.";
    header("Location: consultaCurso.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cursoNome = $_POST['curso_nome'];
    $cursoStatus = $_POST['curso_status'];

    try {
        $sql = "UPDATE curso SET curso_nome = :cursoNome, curso_status = :cursoStatus WHERE idcurso = :idCurso";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':cursoNome', $cursoNome);
        $stmt->bindParam(':cursoStatus', $cursoStatus);
        $stmt->bindParam(':idCurso', $idCurso);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Curso atualizado com sucesso!";
            $_SESSION['tipo_mensagem'] = "sucesso";
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar o curso.";
            $_SESSION['tipo_mensagem'] = "erro";
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro ao atualizar curso: " . $e->getMessage();
        $_SESSION['tipo_mensagem'] = "erro";
    }

            header("Location: consultaCurso.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>../css/estilos.css">
    <meta charset="UTF-8">
    <title>Editar Curso</title>
    <style>
        body{
            display: flex;
            justify-content: center;
            text-align: center;
        }
        .toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            padding: 15px;
            border-radius: 5px;
            opacity: 0;
            transition: opacity 0.5s, bottom 0.5s;
            z-index: 1000;
            text-align: center;
        }

        .toast.show {
            opacity: 1;
            bottom: 30px;
        }

        .toast.sucesso {
            background-color: #4CAF50;
        }

        .toast.erro {
            background-color: #f44336;
        }
        @media (max-width: 498px){
        form{
            width: 85%;
        }
    }
    </style>
</head>

<body>

    <?php
    if (isset($_SESSION['mensagem_erro'])) {
        echo "<p style='color: red;'>" . $_SESSION['mensagem_erro'] . "</p>";
        unset($_SESSION['mensagem_erro']);
    }
    ?>

    <h1>Editar Curso</h1>
    <form method="post">
        <input type="hidden" name="idcurso" value="<?= htmlspecialchars($idCurso) ?>">
        <label for="curso_nome">Nome do Curso:</label>
        <input type="text" name="curso_nome" id="curso_nome" value="<?= htmlspecialchars($curso_nome) ?>" required><br><br>

        <label for="curso_status">Status:</label>
        <select name="curso_status" id="curso_status" required>
            <option value="Ativo" <?= ($curso_status === 'Ativo') ? 'selected' : '' ?>>Ativo</option>
            <option value="Inativo" <?= ($curso_status === 'Inativo') ? 'selected' : '' ?>>Inativo</option>
        </select><br><br>

        <input type="submit" value="Salvar Alterações">
    </form>

    <div id="toast" class="toast"></div>

    <script>
        <?php if (isset($_SESSION['mensagem'])): ?>
            let mensagem = "<?= addslashes($_SESSION['mensagem']); ?>";
            let tipoMensagem = "<?= $_SESSION['tipo_mensagem']; ?>";
            let toast = document.getElementById('toast');

            function showToast(message, type) {
                toast.innerHTML = message;
                toast.classList.add('show', type);
                setTimeout(() => {
                    toast.classList.remove('show', type);
                }, 3000);
            }

            showToast(mensagem, tipoMensagem);

            <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
        <?php endif; ?>
    </script>

</body>

</html>
<?php ob_end_flush(); ?>