<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');

include __DIR__ . '/../sidebar.php';

include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador', 'Professor']);

$showModal = false; // Variável de controle para exibir o modal

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT idtipomaquina, tipomaquina_nome FROM tipomaquina";
    $stmt = $pdo->query($sql);
    $tipos_maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Falha na conexão: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fabricante = $_POST['fabricante'];
    $modelo = $_POST['modelo'];
    $potencia = $_POST['potencia'];
    $tensao = $_POST['tensao'];
    $corrente = $_POST['corrente'];

    $sql = "INSERT INTO motor (motor_fabricante, motor_modelo, motor_potencia, motor_tensão, motor_corrente) 
            VALUES (:fabricante, :modelo, :potencia, :tensao, :corrente)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':fabricante', $fabricante);
    $stmt->bindParam(':modelo', $modelo);
    $stmt->bindParam(':potencia', $potencia);
    $stmt->bindParam(':tensao', $tensao);
    $stmt->bindParam(':corrente', $corrente);

    if ($stmt->execute()) {
        $showModal = true;
    } else {
        echo "Erro ao cadastrar o motor.";
    }
}

$pdo = null;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Motor</title>
    <style>
        form{
            padding: 25px;
            width: 500px;
        }
        .container-cadastro-motor {
            padding-top: 80px;
            text-align: center;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        h2{
            font-size: 30px;
        }

        .modal {
            background: #fff;
            color: black;
            width: 400px;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.3s ease;
        }

        .modal h2 {
            color: black;
        }

        .modal p {
            margin: 15px 0;
            font-size: 1.1em;
        }

        .modal button {
            background-color: #d41414;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 1em;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .modal button:hover {
            background-color: #e61f1f;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .Pdf-ajuda {
            text-align: center;
        }

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

        .Pdf-ajuda a:hover {
            background-color: #fd2020;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }
        @media (max-width: 550px) {
            form {
                max-width: 345px;
            }
            .Pdf-ajuda{
                width: 350px;
            }
        }
    </style>
</head>

<body>
    <div class="container-cadastro-motor">
        <h2>Cadastro de Motor</h2>
        <form method="post" class="form-motor-cadastro">
            <label>Fabricante:</label>
            <input required type="text" name="fabricante">

            <label>Modelo:</label>
            <input required type="text" name="modelo">

            <label>Potência:</label>
            <input required type="number" step="0.01" name="potencia">

            <label>Tensão:</label>
            <input required type="text" name="tensao">

            <label>Corrente:</label>
            <input required type="text" name="corrente">

            <input type="submit" value="Cadastrar">
        </form>

        <div class="Pdf-ajuda">
            <a href="/nr12/ajuda/CADASTRAR MOTOR.pdf" target="_blank">Está com dificuldade de cadastrar um Motor? Clique aqui.</a><br>
            <a href="/nr12/ajuda/ALTERAÇÃO DE MOTOR.pdf" target="_blank">Cadastrou algum motor errado e precisa fazer alguma alteração? Clique aqui</a>
        </div>
    </div>

    <?php if ($showModal): ?>
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <h2>Sucesso!</h2>
            <p>Motor cadastrado com sucesso!</p>
            <button onclick="closeModal()">Fechar</button>
        </div>
    </div>

    <script>
        document.getElementById('modalOverlay').style.display = 'flex';

        function closeModal() {
            document.getElementById('modalOverlay').style.display = 'none';
            window.location.href = 'cadastro.php';
        }
    </script>
    <?php endif; ?>
</body>

</html>
