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

// Consulta para buscar as unidades ativas
$sql = "SELECT idunidade, unidade_nome FROM unidade WHERE unidade_status = 'Ativo'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar o formulário se o método POST for usado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coletando os dados do formulário
    $setor_nome = $_POST['setor_nome'];
    $unidade_id = $_POST['unidade_id'];

    // Preparando a consulta SQL para inserir os dados
    $sql = "INSERT INTO setor (setor_nome, unidade_id) 
            VALUES (:setor_nome, :unidade_id)";
    $stmt = $pdo->prepare($sql);

    // Bind dos parâmetros
    $stmt->bindParam(':setor_nome', $setor_nome);
    $stmt->bindParam(':unidade_id', $unidade_id);

    // Executar a consulta
    $resultado = $stmt->execute();
}

// Fechar a conexão (opcional)
$pdo = null;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Setor</title>
    <link rel="stylesheet" href="../css/estilos.css"> <!-- Certifique-se de incluir seu CSS -->
</head>
<style>
    form{
        padding: 20px;
        width: 500px;
    }
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
        /* Cor de fundo vermelha mais escura ao passar o mouse */
    }

    .container-cadastro-setor {
        text-align: center;
        margin-top: 100px;
    }


    @media (max-width: 540px) {
        .formulario-cadastro-setor {
            width: 335px;
        }

        .container-cadastro-setor {
           margin-top: 0;
            text-align: center;
        }
        body{
            margin-top: 60px;
            height: auto;
        }
        .Pdf-ajuda{
            width: 350px;
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

    /* Modal estilos */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        color: black;
    }

    .modal-content {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        max-width: 500px;
        width: 100%;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .modal-content h2 {
        margin-top: 0;
    }

    .close-modal {
        background: #e21616;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        margin-top: 20px;
    }

    .close-modal:hover {
        background-color: #b01313;
    }

</style>

<body>
    <div id="modal" class="modal">
        <div class="modal-content">
            <h2 id="modal-title">Mensagem</h2>
            <p id="modal-message">Detalhes da mensagem.</p>
            <button class="close-modal" onclick="closeModal()">Fechar</button>
        </div>
    </div>

    <div class="container-cadastro-setor">
        <h1>Cadastro de Setor</h1>
        <form action="cadastro.php" method="post" class="formulario-cadastro-setor">
            <label>Nome do Setor:</label>
            <input type="text" name="setor_nome" required>

            <label>Unidade:</label>
            <select name="unidade_id" required>
                <option value="">Selecione a Unidade</option>
                <?php foreach ($unidades as $unidade): ?>
                    <option value="<?php echo $unidade['idunidade']; ?>">
                        <?php echo htmlspecialchars($unidade['unidade_nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>


            <!-- Adicionando a classe edit-button ao botão de envio -->
            <button type="submit" class="edit-button">Cadastrar</button>
        </form>

        <div class="Pdf-ajuda">
            <a href="/nr12/ajuda/setor.pdf" target="_blank">Está com dificuldade de cadastrar um
                Setor? Clique aqui.</a><br>
            <a href="/nr12/ajuda/ALTERAÇÃO DE SETOR.pdf" target="_blank">Cadastrou algum Setor errado e
                precisa fazer alguma alteração? Clique aqui</a>
        </div>
    </div>

        <script>
        function openModal(title, message) {
            document.getElementById('modal-title').innerText = title;
            document.getElementById('modal-message').innerText = message;
            document.getElementById('modal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

        // PHP passa o resultado para o JavaScript
        <?php if (isset($resultado)): ?>
            <?php if ($resultado): ?>
                openModal("Cadastrado", "Setor cadastrado com sucesso!");
            <?php else: ?>
                openModal("Erro", "Erro ao cadastrar o setor.");
            <?php endif; ?>
        <?php endif; ?>
    </script>


</body>

</html>