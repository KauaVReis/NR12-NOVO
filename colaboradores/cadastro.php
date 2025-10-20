<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo $_SESSION['colaborador_permissao'];

require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador']);

// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');


if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    $mensagem_tipo = $_SESSION['mensagem_tipo'];
    unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']);
}
?>
<?php include __DIR__ . '/../sidebar.php'; ?>

<!DOCTYPE html>
<html lang="pt-BR">
<style>
    body {
        margin-top: 80px;
    }

    form {
        width: 100%;
        padding: 25px;
     
    }

    /* Inputs de texto, email, data, etc. */
    form input[type="password"],
    form input[type="text"],
    form input[type="date"],
    form input[type="email"],
    form input[type="number"] {
        width: 90%;
        padding: 10px;
        margin: 12px 0;
        border: 2px solid #d1d5db;
        border-radius: 6px;
        background-color: #ffffff;
        font-size: 1rem;
        color: #1f2937;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    form input[type="text"]:focus,
    form input[type="date"]:focus,
    form input[type="email"]:focus,
    form input[type="number"]:focus {
        border-color: #e21616;
        box-shadow: 0 4px 8px rgba(79, 70, 229, 0.1);
        outline: none;
    }

    /* Campo NIF oculto inicialmente */
    .nif {
        width: 100%;
    }

    /* Seletor estilizado */
    select {
        appearance: none;
        width: 90%;
        padding: 12px;
        margin: 12px 0;
        border: 2px solid #d1d5db;
        border-radius: 6px;
        background-color: #ffffff;
        color: #1f2937;
        cursor: pointer;
        transition: border-color 0.3s, box-shadow 0.3s;
        background: url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 10" width="14" height="10"%3E%3Cpath d="M1 1l6 6 6-6" fill="%23e21616"%3E%3C/path%3E%3C/svg%3E') no-repeat right 10px center;
        background-size: 12px;
    }

    select:hover {
        border-color: #e21616;
    }

    select:focus {
        border-color: #e21616;
        outline: none;
    }

    /* Botões */
    input[type="submit"] {
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

    input[type="submit"]:hover {
        background-color: #fd2020;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        transform: translateY(-3px);
    }

    /* Títulos */
    h2 {
        margin-bottom: 20px;
        font-size: 1.8rem;
        color: #1f2937;
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }

    /* Estilização para labels */
    label {
        display: block;
        margin-top: 12px;
        font-weight: bold;
        color: #4b5563;
        font-size: 0.9rem;
    }



    /* Responsividade */
    @media (max-width: 768px) {
        .Pdf-ajuda a {
            display: inline-block;
             margin: 8px 20px !important; 
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

        form {
            width: 90%;
            padding: 20px;
            margin: 0;
        }

        input[type="text"],
        input[type="number"],
        input[type="email"],
        select,
        input[type="submit"] {
            font-size: 1rem;
            padding: 10px 16px;
        }
        h2{
            font-size: 25px;
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



<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Funcionário</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <script>
        function mostrarCampoNIF() {
            var tipoFuncionario = document.getElementById("tipo_funcionario").value;
            var campoNIF = document.getElementById("campo_nif");

            if (tipoFuncionario === "Professor") {
                campoNIF.style.display = "block";
                document.getElementById("nif").setAttribute("required", "required"); // Adiciona o required
            } else {
                campoNIF.style.display = "none";
                document.getElementById("nif").removeAttribute("required"); // Remove o required
            }
        }
    </script>

</head>

<body>

    <div class="container-cadastro-funcionario">
        <h2>CADASTRO DE FUNCIONÁRIO</h2>
        <form action="./registro.php" method="post">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" placeholder="Digite o nome do funcionário:" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Digite o Email do funcionário:" required>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" value="senaisp" readonly name="senha" required>


            <label for="tipo_funcionario">Tipo de Funcionário:</label>
            <select id="tipo_funcionario" required name="tipo_funcionario">
                <option value="">Selecione</option>
                <option value="Adm">Adm</option>
                <option value="Professor">Professor</option>
                <option value="Coordenador">Coordenador</option>
                <option value="Manutencao">Manutencao</option>
            </select>

            <div class="nif">
                <label for="nif">NIF:</label>
                <input type="text" id="nif" name="nif" inputmode="numeric" pattern="[0-9]*" maxlength="7" required
                    oninput="this.value = this.value.replace(/[^0-9]/g, '');">
            </div>

            <label for="setor">Setor:</label>
            <select id="setor" name="setor" required>
                <option value="">Selecione um setor</option>
                <?php
                include '../conexao.php';
                $sql = "SELECT idsetor, setor_nome FROM setor WHERE setor_status = 'Ativo'";
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . htmlspecialchars($row['idsetor']) . "'>" . htmlspecialchars($row['setor_nome']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Nenhum setor disponível</option>";
                    }
                } catch (PDOException $e) {
                    echo "<option value=''>Erro ao buscar setores: " . htmlspecialchars($e->getMessage()) . "</option>";
                }
                ?>
            </select>

            <input type="submit" value="Cadastrar">
        </form>

        <?php if (isset($mensagem)): ?>
            <div class="modal <?php echo $mensagem_tipo; ?>"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
    </div>

    <div class="Pdf-ajuda">
        <a href="/nr12/ajuda/CADASTRO DE FUNCIONÁRIO.pdf" target="_blank">Está com dificuldade de cadastrar um
            funcionário? Clique aqui.</a><br>
        <a href="/nr12/ajuda/ALTERAÇÃO DE FUNCIONÁRIO.pdf" target="_blank">Cadastrou algum funcionário errado e
            precisa fazer alguma alteração? Clique aqui</a>
    </div>

    <script>

        window.addEventListener('DOMContentLoaded', (event) => {
            const modal = document.querySelector('.modal');
            if (modal) {
                // Configura o modal para desaparecer após 3 segundos (3000 ms)
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 2000);
            }
        });



    </script>
</body>

</html>