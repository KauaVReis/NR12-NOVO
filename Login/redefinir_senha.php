<?php
session_start();
include '../conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$usuarioId = $_SESSION['user_id'];
$erroSenha = false;
$senhaAlteradaComSucesso = false;
$mensagemErro = ""; // Inicializa a variável de mensagem de erro

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $novaSenha = $_POST['nova_senha'];

    // Verifica a força da senha
    $uppercase = preg_match('@[A-Z]@', $novaSenha);
    $lowercase = preg_match('@[a-z]@', $novaSenha);
    $number    = preg_match('@[0-9]@', $novaSenha);
    $specialChars = preg_match('@[^\w]@', $novaSenha);

    if (strlen($novaSenha) < 8 || !$uppercase || !$lowercase || !$number || !$specialChars) {
        $erroSenha = true;
        $mensagemErro = "A senha deve ter no mínimo 8 caracteres, letras maiúsculas e minúsculas, números e caracteres especiais.";
    } elseif ($novaSenha != 'senaisp') {
        // Cria o hash da nova senha

        $opcoes = [
            'memory_cost' => 1<<17,    // 128 MB de memória (configuração moderada)
            'time_cost'   => 4,        // Tempo de processamento
            'threads'     => 2         // Paralelismo
        ];
        $novaSenhaHash = password_hash($novaSenha, PASSWORD_ARGON2ID);
        // Atualiza a senha no banco de dados
        $stmt = $pdo->prepare("UPDATE colaborador SET senha = ?, senha_padrao = 0 WHERE idcolaborador = ?");
        $stmt->bindParam(1, $novaSenhaHash, PDO::PARAM_STR);
        $stmt->bindParam(2, $usuarioId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $senhaAlteradaComSucesso = true;
            unset($_SESSION['redefinir_senha']); // Remove a flag de redefinição de senha
        } else {
            echo "Erro ao alterar a senha: " . $stmt->error; // Mantenha a exibição de erros SQL para debug
        }
        $stmt->closeCursor(); // libera recursos de consulta para melhor gerenciamento de memória 
    } else {
        $erroSenha = true;
        $mensagemErro = "A nova senha não pode ser a senha padrão.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" href="images/Logo-icon.png" type="image/x-icon">
    <title>Redefinir Senha</title>
    <style>
        * {
            margin: 0;
            /* Remove margens padrao */
            padding: 0;
            /* Remove preenchimentos padrao */
            box-sizing: border-box;
            /* Inclui bordas e padding no calculo de largura/altura */
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* Estilos para o cabesalho e formulario de cadastro */
        h1 {
            color: black;
            /* Cor do texto */
            padding: 30px;
            text-align: center;
            justify-content: center;
            /* Preenchimento */
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #dddddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }


        /* Estilos para rotulos no formulario */
        form label {
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        /* Estilos para entradas de texto no formulario */
        form input {
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            width: 100%;
        }

        form input[type="submit"] {
            padding: 10px;
            background-color: #b80202;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            width: 50%;
        }


        form input:focus {
            border-color: #000000;
            /* Cor da borda ao focar */
            outline: none;
            /* Sem contorno */
        }

        /* Estilos para botoes no formulario */
        form button {
            padding: 10px;
            background-color: #b80202;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-left: -10px;
            margin-right: -30px;
        }

        button {
            padding: 10px;
            background-color: #b80202;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #000000;
        }

        form button:hover {
            background-color: #000000;
            /* Cor de fundo ao passar o mouse */
        }

        /* Estilos do modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            text-align: center;
            border-radius: 8px;
        }

        .close-btn {
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .error {
            color: red;
        }

        #togglePassword {
            cursor: pointer;
            position: absolute;
            right: 10px;
            /* Para posicionar à direita do campo */
            top: 8px;
            /* Ajuste conforme necessário para alinhar verticalmente */
            color: #999;
            /* Cor do ícone */
            font-size: 20px;
            /* Tamanho do ícone */
        }

        .input-group {
            position: relative;
            /* Para posicionar o ícone de olho */
        }
    </style>
</head>

<body>
    <h1>Redefinir Senha</h1>
    <form method="post">
        <label for="nova_senha">Nova Senha:</label>
        <div class="input-group">
            <input type="password" id="nova_senha" name="nova_senha" required><br>
            <span id="togglePassword" onclick="togglePasswordVisibility()"><i class="fa-solid fa-eye-slash"></i></span> <!-- Ícone de olho -->
        </div>

        <?php if ($erroSenha): ?>
            <p class="error"><?php echo $mensagemErro; ?></p>
        <?php endif; ?>

        <input type="submit" value="Redefinir Senha">
    </form>

    <!-- Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <h2>Senha alterada com sucesso!</h2>
            <button class="close-btn" onclick="fecharModal()">OK</button>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            var passwordInput = document.getElementById("nova_senha");
            var toggleIcon = document.getElementById("togglePassword");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.innerHTML = '<i class="fa-solid fa-eye"></i>'; // Ícone de olho aberto
            } else {
                passwordInput.type = "password";
                toggleIcon.innerHTML = '<i class="fa-solid fa-eye-slash"></i>'; // Ícone de olho fechado com barra
            }
        }

        function fecharModal() {
            document.getElementById('successModal').style.display = 'none';
            window.location.href = '../home.php'; // Redireciona para o painel
        }

        <?php if ($senhaAlteradaComSucesso): ?>
            document.getElementById('successModal').style.display = 'block';
        <?php endif; ?>
    </script>
</body>

</html>