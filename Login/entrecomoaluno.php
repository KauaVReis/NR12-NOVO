    <!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entre Como Aluno</title>
    <style>
     .body_login {
        background-color: #A5322C;
        font-family: sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        position: relative;
        overflow: hidden;
    }

    .body_login::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 40%;
        height: 30%;
        background: linear-gradient(to bottom right, #9C2E28, #C9302C);
        clip-path: polygon(0 0, 100% 0, 0 100%);
        z-index: -1;
    }

    .body_login::after {
        content: "";
        position: absolute;
        bottom: 0;
        right: 0;
        width: 50%;
        height: 40%;
        background: linear-gradient(to top left, #9C2E28, #C9302C);
        clip-path: polygon(100% 0, 100% 100%, 0 100%);
        z-index: -1;
    }

    .container2_login {
        background-color: #dedede;
        display: flex;
        align-items: center;
        flex-direction: column;
        border-radius: 20px;
        padding: 35px;
        padding-top: 50px;
        text-align: center;
        box-shadow: 5px 8px 5px 8px rgba(0, 0, 0, 0.2);
        max-width: 275px;
        width: 100%;
    }

    .logo_senai_login {
        width: 100%;
        max-width: 300px;
        
    }

    .form_login {
        display: flex;
        align-items: center;
        flex-direction: column;
        gap: 45px;
    }

    .input_login {
        width: 80%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
        box-shadow: 5px 5px 7px 5px rgba(0, 0, 0, 0.2);
        transition: border 0.3s ease;
    }

    .input_login:focus {
        border: 1px solid transparent;
        outline: none;
    }

    .login-button {
        width: 80%;
        background-color: #C9302C;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        box-shadow: 0px 5px 5px 1px rgba(0, 0, 0, 0.2);
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .login-button:hover {
        background-color: #b80404;
        transform: scale(1.06);
    }


    .entrar_aluno {
        font-size: 16px;
        color: #C9302C;
        text-decoration: none;
        font-style: italic;
        font-weight: bolder;
    }

    .entrar_aluno:hover {
        color: #000;
    }

    /* Responsividade */

    @media (max-width: 768px) {
        .container2_login {
            padding: 50px;
            max-width: 27%;
            /* Aumenta a largura do container para ocupar mais espaço */
        }

        .logo_senai_login {
            max-width: 200px;
            /* Reduz o tamanho da logo em telas menores */
        }

        .form_login {
            gap: 30px;
        }

        .input_login,
        .login-button {
            width: 85%;
            font-size: 14px;
            padding: 8px;
        }

        .login-button {
            padding: 8px 15px;
        }

        .entrar_aluno {
            font-size: 14px;
        }
    }

    @media (max-width: 480px) {
        .container2_login {
          height: 250px;
        }

        .logo_senai_login {
            width: 140px;
            /* Reduz ainda mais o tamanho da logo para dispositivos muito pequenos */
        }

        .input_login,
        .login-button {
            font-size: 12px;
            padding: 7px;
            width: 120px;
        }

        .login-button {
            padding: 6px 10px;
        }

        .entrar_aluno {
            font-size: 12px;
        }
        .form_login{
            width: 100%;
          
        }
    }
    </style>
</head>

<body class="body_login">
    <div class="container2_login">
        <div>
            <form action="verificar_matricula.php" method="post" class="form_login">
            <img src="../imagem/senailogo.png" alt="Logo Senai" class="logo_senai_login">
                <input class="input_login" type="text" id="matricula" name="matricula" placeholder="Matrícula do aluno" class="input" required>
                <input  class="input_login" type="text" id="nimaquina" name="nimaquina" placeholder="NI da máquina" class="input" required>
                <input class="login-button" type="submit" value="Entrar">
                <a class="entrar_aluno" href="login.php">Entre como Colaborador</a>
            </form>
        </div>
    </div>
</body>

</html>