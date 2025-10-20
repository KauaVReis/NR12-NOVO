<?php
include '../conexao.php'; // Verifique se o caminho está correto

$base_dir = dirname($_SERVER['SCRIPT_NAME']);

// Adiciona uma barra no final se não houver
$base_dir = rtrim($base_dir, '/') . '/';

// Corrige a URL para sempre começar do diretório raiz do projeto
define('BASE_URL', '../../nr12/');
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/nr12/sidebar.php'; ?>
<?php

require_once '../verifica_permissao.php';
verificaPermissao(['Adm']);

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Adicione a cláusula WHERE para filtrar pela turma com o ID fornecido
    $sql = "SELECT t.idturmas, t.turma_nome, t.turma_periodo, t.turma_inicio, t.turma_fim, t.curso_id, c.curso_nome, co.colaborador_nome, t.colaborador_id
    FROM turmas t
    LEFT JOIN curso c ON t.curso_id = c.idcurso
    LEFT JOIN colaborador co ON t.colaborador_id = co.idcolaborador
    WHERE t.idturmas = :id";


    try {
        $stmt = $pdo->prepare($sql);
        // Vincule o parâmetro :id
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $turmas = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            echo "Turma não encontrada.";
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

$sqlCursos = "SELECT idcurso, curso_nome FROM curso WHERE curso_status ='Ativo'";
$cursos = $pdo->query($sqlCursos)->fetchAll(PDO::FETCH_ASSOC);

$sqlcolaboradores = "SELECT idcolaborador, colaborador_nome FROM colaborador WHERE colaborador_status = 'Ativo  ' AND colaborador_permissao = 'Professor'";
$colaboradores = $pdo->query($sqlcolaboradores)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Editar Turma</title>
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
            height: 50px;
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

        @media (max-width: 480px) {
            form{
                width: 87%;
            }
            form input[type="date"]{
                min-width: 77%;

            }
         
        }
    </style>
</head>

<body>
    <h2>Editar Turma</h2>

    <form action="atualizar.php" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($turmas['idturmas']); ?>">

        <label for="turma_nome">Nome:</label>
        <input type="text" id="turma_nome" name="turma_nome"
            value="<?php echo htmlspecialchars($turmas['turma_nome']); ?>" required>

        <label for="turma_periodo">Período:</label>
        <select id="turma_periodo" name="turma_periodo" required>
            <option value="Manhã" <?php if ($turmas['turma_periodo'] === 'Manhã')
                echo 'selected'; ?>>Manhã</option>
            <option value="Tarde" <?php if ($turmas['turma_periodo'] === 'Tarde')
                echo 'selected'; ?>>Tarde</option>
            <option value="Noite" <?php if ($turmas['turma_periodo'] === 'Noite')
                echo 'selected'; ?>>Noite</option>
            <option value="Integral" <?php if ($turmas['turma_periodo'] === 'Integral')
                echo 'selected'; ?>>Integral
            </option>
        </select>

        <label for="turma_inicio">Início:</label>
        <input type="date" id="turma_inicio" name="turma_inicio"
            value="<?php echo htmlspecialchars($turmas['turma_inicio']); ?>" required>

        <label for="turma_fim">Fim:</label>
        <input type="date" id="turma_fim" name="turma_fim" value="<?php echo htmlspecialchars($turmas['turma_fim']); ?>"
            required>

        <label for="curso_id">Curso:</label>
        <select id="curso_id" name="curso_id" required>
            <?php foreach ($cursos as $curso): ?>
                <option value="<?php echo htmlspecialchars($curso['idcurso']); ?>" <?php if ($turmas['curso_id'] == $curso['idcurso'])
                       echo 'selected'; ?>>
                    <?php echo htmlspecialchars($curso['curso_nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="colaborador_id">Colaborador:</label>
        <select id="colaborador_id" name="colaborador_id" required>
            <?php foreach ($colaboradores as $colaborador): ?>
                <option value="<?php echo htmlspecialchars($colaborador['idcolaborador']); ?>" <?php if ($turmas['colaborador_id'] == $colaborador['idcolaborador'])
                       echo 'selected'; ?>>
                    <?php echo htmlspecialchars($colaborador['colaborador_nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>

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
                        window.location.href = "http://localhost/nr12/turmas/consulta.php";
                    }, 3000);
                }

                showToast(mensagem, tipoMensagem);

                <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
            <?php endif; ?>
        </script>

        <input type="submit" value="Atualizar">
    </form>
    <!-- Botão de voltar -->
    <button onclick="window.location.href='consulta.php';" class='voltar'>Voltar Para Consulta</button>
</body>

</html>