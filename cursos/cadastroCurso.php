<?php
session_start();
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);

// Adiciona uma barra no final se não houver
$base_dir = rtrim($base_dir, '/') . '/';

// Corrige a URL para sempre começar do diretório raiz do projeto
// define('BASE_URL', '../../nr12/');


?>
<?php include __DIR__ . '/../sidebar.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        form{
            padding: 20px;
            width: 500px;
        }
        body{
            display: flex;
            text-align: center;
            justify-content: center;
        }
        
        h1 {
            text-align: center;
        }

        .toast {
            margin-top: 400px !important;
        }

        .container_cadastro_curso {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 768px) {

            form {
                width: 100%;
                padding: 35px;
                margin-top: 35px;
            }
        }
        @media (max-width: 498px) {
            .Pdf-ajuda {
                    width: 340px
            }
        }
        /* Estilo para a div que contém os links dos PDFs */
        .Pdf-ajuda {
            text-align: center;
            
        }

        /* Estilo para os links dentro da div */
        .Pdf-ajuda a {
            display: inline-block;
            margin: 8px 0;
            padding: 10px 20px;
            color: #ffffff;
            background-color: #e21616;
            text-decoration: none;
            font-weight: bold;
            border-radius: 6px;
            transition: background-color 0.3s, box-shadow 0.3s;
            text-transform: uppercase;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        /* Hover para o link, com um efeito de sombra e cor mais clara */
        .Pdf-ajuda a:hover {
            background-color: #fd2020;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        /* Espaço e alinhamento para o texto */
        .Pdf-ajuda a:focus {
            outline: none;
        }
    </style>

</head>

<body>
    <h1>Cadastro de Curso</h1>
    <!-- Formulário de cadastro de curso -->
    <div class="container_cadastro_curso">
        <form method="POST" action="">
            <label for="nome">Nome do Curso:</label>
            <input type="text" name="nome" required><br><br>

            <input type="submit" value="Cadastrar Curso">
        </form>
    </div>

    <div class="Pdf-ajuda">
        <a href="../ajuda/CADASTRO DE CURSO.pdf" target="_blank">Está com dificuldade de cadastrar um
            curso? Clique aqui.</a><br>
        <a href="../ajuda/ALTERAÇÃO DE CURSO.pdf" target="_blank">Cadastrou algum curso errado e
            precisa fazer alguma alteração? Clique aqui</a>
    </div>

    <div id="toast" class="toast"></div>

    <?php
    include '../conexao.php';
    require_once '../verifica_permissao.php';
    verificaPermissao(['Adm', 'Coordenador']);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nomeCurso = $_POST['nome'];

        if (!empty($nomeCurso)) {
            $sql_verificar = "SELECT COUNT(*) FROM curso WHERE curso_nome = ?";
            $stmt_verificar = $pdo->prepare($sql_verificar);
            $stmt_verificar->execute([$nomeCurso]);
            $count = $stmt_verificar->fetchColumn();

            if ($count > 0) {
                $mensagem = "Curso já cadastrado!";
                $tipo_mensagem = "erro";
            } else {
                $sql = "INSERT INTO curso (curso_nome) VALUES (?)";
                $stmt = $pdo->prepare($sql);

                if ($stmt->execute([$nomeCurso])) {
                    $mensagem = "Curso cadastrado com sucesso!";
                    $tipo_mensagem = "sucesso";
                } else {
                    $mensagem = "Erro ao cadastrar o curso!";
                    $tipo_mensagem = "erro";
                }
            }
        } else {
            $mensagem = "O campo nome é obrigatório!";
            $tipo_mensagem = "erro";
        }

        $_SESSION['mensagem'] = $mensagem;
        $_SESSION['tipo_mensagem'] = $tipo_mensagem;
    }
    ?>

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
                    // Redireciona para a mesma página após mostrar a mensagem
                    window.location = "<?= $_SERVER['PHP_SELF']; ?>";
                }, 3000); // Exibe por 3 segundos
            }

            showToast(mensagem, tipoMensagem);

            <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
        <?php endif; ?>
    </script>
</body>

</html>