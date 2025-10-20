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
verificaPermissao(['Adm', 'Coordenador']);

try {
    // Definindo o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Falha na conexão: " . $e->getMessage());
}

// Inicializa uma variável de mensagem vazia
$mensagem = '';
$tipoMensagem = ''; // 'sucesso' ou 'erro'

// Processar o formulário se o método POST for usado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coletando os dados do formulário
    $nome = $_POST['unidade_nome'];
    $cidade = $_POST['unidade_cidade'];
    $estado = $_POST['unidade_estado'];
    $numero = $_POST['unidade_numero'];

    // Preparando a consulta SQL para inserir os dados
    $sql = "INSERT INTO unidade (unidade_nome, unidade_cidade, unidade_estado, unidade_numero) 
            VALUES (:nome, :cidade, :estado, :numero)";
    $stmt = $pdo->prepare($sql);

    // Bind dos parâmetros
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':cidade', $cidade);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':numero', $numero);

    // Executar a consulta
    if ($stmt->execute()) {
        $mensagem = "Unidade cadastrada com sucesso!";
        $tipoMensagem = 'sucesso';
    } else {
        $mensagem = "Erro ao cadastrar a unidade.";
        $tipoMensagem = 'erro';
    }
}

// Fechar a conexão (opcional)
$pdo = null;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilos.css">
    <title>Cadastro de Unidade</title>
    <style>
        h1 {
            text-align: center;
        }

        .container_cadastro_curso {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: auto;
        }

        form {
            max-width: 400px;
            /* Limita a largura do formulário */
            padding: 20px;
            margin-top: 35px;
            background-color: #f9f9f9;
            /* Cor de fundo do formulário */
            border-radius: 8px;
            /* Bordas arredondadas */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            /* Sombra do formulário */
        }

        label {
            margin-top: 10px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            margin-top: 15px;
            padding: 10px;
            background-color: #e21616;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s, box-shadow 0.3s;
            font-weight: bold;
            text-transform: uppercase;
            width: 60%;
        }

        button:hover {
            background-color: #fd2020;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            form {
                width: 100%;
                padding: 20px;
                margin-top: 35px;
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
        @media (max-width: 498px) {
            form {
                width: 85%;
               
            }
            .Pdf-ajuda{
                width: 90%;
            }
            .container_cadastro_curso{
                margin-top: 60px;
            }
        }

    </style>
</head>

<body>
    <div class="container_cadastro_curso">
        <h1>Cadastro de Unidade</h1>
        <form action="cadastro.php" method="post">
            <label>Nome da Unidade:</label>
            <input type="text" name="unidade_nome" required>

            <label>Cidade:</label>
            <input type="text" name="unidade_cidade" required>

            <label>Estado:</label>
            <input type="text" name="unidade_estado" required>

            <label>Número:</label>
            <input type="number" name="unidade_numero" required>

            <button type="submit">Cadastrar Unidade</button>
        </form>

        <!-- Toast de Mensagem -->
        <div class="modal <?php echo $tipoMensagem; ?>">
            <?php echo $mensagem; ?>
        </div>

        <div class="Pdf-ajuda">
            <a href="/nr12/ajuda/CADASTRO DE UNIDADE.pdf" target="_blank">Está com dificuldade de cadastrar uma
                unidade? Clique aqui.</a><br>
            <a href="/nr12/ajuda/ALTERAÇÃO DE UNIDADE.pdf" target="_blank">Cadastrou alguma unidade errada e
                precisa fazer alguma alteração? Clique aqui</a>
        </div>
    </div>

    <script>
        // Exibir o toast se houver mensagem
        window.addEventListener('DOMContentLoaded', (event) => {
            const modal = document.querySelector('.modal');
            if (modal) {
                // Configura o modal para desaparecer após 3 segundos (3000 ms)
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 3000);
            }
        });
    </script>
</body>

</html>