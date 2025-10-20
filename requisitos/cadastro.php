<?php
// Certifique-se de que não há nenhuma saída antes deste ponto

// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');

// Evitar qualquer saída antes do header()
ob_start(); // Inicia o buffer de saída

include __DIR__ . '/../sidebar.php';
include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador', 'Professor']);

$mensagem = false; // Variável para controle do modal

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificando se o formulário foi enviado
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $requisito_topico = $_POST['requisito_topico'];
        $tipo_req = $_POST['tipo_req'];

        // Inserindo o requisito no banco de dados
        $sql = "INSERT INTO requisitos (requisito_topico, tipo_req) VALUES (:requisito_topico, :tipo_req)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':requisito_topico', $requisito_topico);
        $stmt->bindParam(':tipo_req', $tipo_req);
        $stmt->execute();

        // Redireciona com um parâmetro para exibir o modal
        header("Location: " . $_SERVER['PHP_SELF'] . "?sucesso=1");
        exit;
    }
} catch (PDOException $e) {
    // Log do erro (opcional)
    error_log($e->getMessage());
}

ob_end_flush(); // Libera a saída buffered
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
      form {
            padding: 25px;
            width: 500px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            width: 400px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
            font-family: Arial, sans-serif;
        }

        .modal-content h2 {
            margin-bottom: 20px;
            color: #4CAF50;
            font-size: 24px;
            font-weight: bold;
        }

        .modal-content p {
            margin-bottom: 20px;
            font-size: 18px;
            line-height: 1.6;
            color: #333;
        }

        .modal-content button {
            padding: 10px 25px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .modal-content button:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }

        .modal-content button:active {
            transform: scale(1);
        }

        .container_requisitos {
            padding-top: 250px;
        }
        @media (max-width: 540px) {
            .form-requis {
                width: 100%;
                max-width: 350px;
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
    <div class="container_requisitos">
        <h1>Cadastro de Requisito</h1>

        <form action="" method="POST" class="form-requis">
            <label for="requisito_topico">Tópico do Requisito:</label>
            <input type="text" id="requisito_topico" name="requisito_topico" required>

            <label for="tipo_req">Tipo do Requisito:</label>
            <select id="tipo_req" name="tipo_req" required>
                <option value="Seguranca">Segurança</option>
                <option value="Operacional">Operacional</option>
                <option value="Preventivo">Preventivo</option>
            </select>

            <input type="submit" value="Cadastrar Requisito"></input>
        </form>
    </div>

    <div class="Pdf-ajuda">
        <a href="/nr12/ajuda/CADASTRO DE REQUISITOS.pdf" target="_blank">Está com dificuldade de cadastrar um
            requisito? Clique aqui.</a><br>
        <a href="/nr12/ajuda/ALTERAÇÃO DE REQUISITOS.pdf" target="_blank">Cadastrou algum requisito errado e
            precisa fazer alguma alteração? Clique aqui</a>
    </div>

    <div id="modal" class="modal">
        <div class="modal-content">
            <h2>Sucesso!</h2>
            <p>O requisito foi cadastrado com sucesso. Agora ele está disponível no sistema.</p>
            <button onclick="fecharModal()">Fechar</button>
        </div>
    </div>
    <script>
    // Função para exibir o modal
    function exibirModal() {
        const modal = document.getElementById('modal');
        modal.style.display = 'flex';
    }

    // Função para fechar o modal
    function fecharModal() {
        const modal = document.getElementById('modal');
        modal.style.display = 'none';
    }

    // Verifica se o parâmetro 'sucesso' está na URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('sucesso')) {
        exibirModal();
        // Remove o parâmetro para evitar reexibição após o próximo reload
        history.replaceState(null, '', window.location.pathname);
    }
</script>

</body>

</html>