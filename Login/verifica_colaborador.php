<?php
session_start();

// Inclua a conexão com o banco de dados
include '../conexao.php';

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pega os valores enviados pelo formulário
    $colaborador_email = $_POST['colaborador_email'];
    $senha = $_POST['senha'];

    try {
        // Consulta o colaborador pelo email
        $sqlColaborador = "SELECT * FROM colaborador WHERE colaborador_email = :colaborador_email";
        $stmtColaborador = $pdo->prepare($sqlColaborador);
        $stmtColaborador->bindParam(':colaborador_email', $colaborador_email);
        $stmtColaborador->execute();

        // Verifica se o colaborador foi encontrado
        if ($stmtColaborador->rowCount() > 0) {
            // Recupera os dados do colaborador
            $colaborador = $stmtColaborador->fetch(PDO::FETCH_ASSOC);

            // Verifica se o colaborador está ativo
            if ($colaborador['colaborador_status'] === 'Inativo') {
                $erro = "Seu usuário está inativo. Por favor, entre em contato com o administrador.";
            }
            // Verifica se a senha está correta
            elseif (password_verify($senha, $colaborador['senha'])) {
                // Verifica se a senha é a padrão e se o campo senha_padrao está ativado
                if ($colaborador['senha_padrao'] == 1 && $senha == 'senaisp') {
                    $_SESSION['user_id'] = $colaborador['idcolaborador'];
                    $_SESSION['colaborador_email'] = $colaborador['colaborador_email'];
                    $_SESSION['colaborador_permissao'] = $colaborador['colaborador_permissao'];
                    $_SESSION['colaborador_nome'] = $colaborador['colaborador_nome'];
                    $_SESSION['redefinir_senha'] = true;

                    // Exibe o modal para redefinição de senha
                    echo "<div id='senhaModal' class='modalverificacolaborador'>
                            <div class='modal-contentverificacolaborador'>
                                <span class='close'>×</span>
                                <p>Você está usando a senha padrão. Por favor, redefina sua senha.</p>
                                <button onclick=\"window.location.href='redefinir_senha.php';\">OK</button> 
                            </div>
                          </div>";
                } else {
                    // Armazena as informações do colaborador na sessão
                    $_SESSION['user_id'] = $colaborador['idcolaborador'];
                    $_SESSION['colaborador_email'] = $colaborador['colaborador_email'];
                    $_SESSION['colaborador_permissao'] = $colaborador['colaborador_permissao'];
                    $_SESSION['colaborador_nome'] = $colaborador['colaborador_nome'];

                    // Redireciona para a página inicial
                    header("Location: ../home.php");
                    exit;
                }
            } else {
                $erro = "E-mail ou senha incorretos. Tente novamente.";
            }
        } else {
            $erro = "E-mail ou senha incorretos. Tente novamente.";
        }
    } catch (PDOException $e) {
        $erro = "Erro ao verificar e-mail e senha: " . $e->getMessage();
    }
}
?>


    <style>
        /* Estilos do modal */
        .modalverificacolaborador{
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            color: black;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-contentverificacolaborador {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            text-align: center;
            border-radius: 8px;
        }

        .close {
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

<link rel="stylesheet" href="../css/estilos.css">
</head>

<body class="verificar-colaborador">
    <div class="container-verif-colab">
        <h1 class="titulo-verif-colab">Verificação de Login</h1>

        <?php if (isset($erro)): ?>
            <p class="message"><?php echo $erro; ?></p>
        <?php endif; ?>
        <button type="submit" class="back-button" onclick="voltar()">Voltar para Login</button>
    </div>

    <script>
        function voltar(){
            window.location.href = 'login.php';
        }

        // Lógica do modal
        var modal = document.getElementById("senhaModal");
        var span = document.getElementsByClassName("close")[0];

        <?php if (isset($_SESSION['redefinir_senha']) && $_SESSION['redefinir_senha'] == true): ?>
            modal.style.display = "block";

            setTimeout(function() {
                window.location.href = 'redefinir_senha.php';
            }, 4000); // Redireciona após 4 segundos

            <?php unset($_SESSION['redefinir_senha']); ?>
        <?php endif; ?>

        span.onclick = function() {
            modal.style.display = "none";
            window.location.href = 'redefinir_senha.php';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
                window.location.href = 'redefinir_senha.php';
            }
        }
    </script>

</body>

</html>
