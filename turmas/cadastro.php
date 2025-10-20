<?php
include '../conexao.php';

// Configuração de diretórios e inclusão da barra lateral
$base_dir = dirname($_SERVER['SCRIPT_NAME']);

// Adiciona uma barra no final se não houver
$base_dir = rtrim($base_dir, '/') . '/';

// Corrige a URL para sempre começar do diretório raiz do projeto
// define('BASE_URL', '../../nr12/');


// Exibir mensagens de sessão se existirem
$mensagem = isset($_SESSION['mensagem']) ? $_SESSION['mensagem'] : '';
$tipo_mensagem = isset($_SESSION['tipo_mensagem']) ? $_SESSION['tipo_mensagem'] : '';

// Limpar a mensagem da sessão após exibir
unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']);
?>

<?php include __DIR__ . '/../sidebar.php'; ?>
<?php
include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador', 'Professor']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>../css/estilos.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <title>Cadastro de Turma</title>
    <style>
        .container-cadastro-turma{
            margin: auto;
        }
        /* Estilos do modal toast */
        .modal-toast {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px;
            color: #fff;
            border-radius: 8px;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.5s, bottom 0.5s;
        }
        form{
            padding: 20px;
            width: 500px;
        }

        .modal-toast.show {
            display: block;
            opacity: 1;
        }

        .modal-toast.sucesso {
            background-color: #4CAF50;
        }

        .modal-toast.erro {
            background-color: #f44336;
        }

        @media (max-width: 498px) {
            .form-cadas-turm {
                width: 100%;
                width: 335px;
                max-width: 500px;
                padding: 20px;
            }
            .Pdf-ajuda {
            width: 83%;
        }
         form input[type="date"],
         form input[type="text"] {
            width: 273px;
            padding: 5px;
         }

            .container-cadastro-turma {
                padding-top: 70px;
                text-align: center;
            }
        }

        /* Estilo para a div que contém os links dos PDFs */
        .Pdf-ajuda {
            display: flex;
            flex-direction: column;
            text-align: center;
            align-items: center;
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
        @media (max-width: 768px) {
  h2 {
    font-size: 28px;
  }
        }
    </style>
</head>

<body>

    <!-- Modal Toast -->
    <div id="modal-toast" class="modal-toast <?= $tipo_mensagem; ?>"><?= $mensagem; ?></div>

    <!-- Formulário de cadastro da turma -->
    <div class="container-cadastro-turma">
        <h2>CADASTRO DA TURMA</h2>
        <form action="./registro.php" method="post" class="form-cadas-turm">
            <label for="turma_nome">Nome da Turma:</label>
            <input type="text" id="turma_nome" name="turma_nome" required>

            <label for="periodo_turma">Período da Turma:</label>
            <select id="periodo_turma" name="periodo_turma" required>
                <option value="">Selecione o Período</option>
                <option value="Manha">Manhã</option>
                <option value="Tarde">Tarde</option>
                <option value="Noite">Noite</option>
                <option value="Integral">Integral</option>
            </select>

            <label for="inicio_turma">Início da Turma:</label>
            <input type="date" id="inicio_turma" name="inicio_turma" required>

            <label for="fim_turma">Fim da Turma:</label>
            <input type="date" id="fim_turma" name="fim_turma" required>

            <label for="colaborador_id">Colaborador:</label>
            <select id="colaborador_id" name="colaborador_id" required>
                <option value="">Selecione um colaborador</option>

                <?php
                // Populando a lista de colaboradores
                $sql = "SELECT idcolaborador, colaborador_nome 
                    FROM colaborador 
                    WHERE colaborador_permissao = 'Professor' 
                    AND colaborador_status = 'Ativo';
                    ";

                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . htmlspecialchars($row['idcolaborador']) . "'>" . htmlspecialchars($row['colaborador_nome']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Nenhum colaborador disponível</option>";
                    }
                } catch (PDOException $e) {
                    echo "<option value=''>Erro ao buscar colaboradores: " . htmlspecialchars($e->getMessage()) . "</option>";
                }
                ?>
            </select>

            <label for="curso_id">Curso:</label>
            <select id="curso_id" name="curso_id" required>
                <option value="">Selecione um curso</option>

                <?php
                // Populando a lista de cursos
                $sql = "SELECT idcurso, curso_nome FROM curso WHERE curso_status = 'Ativo' ";
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . htmlspecialchars($row['idcurso']) . "'>" . htmlspecialchars($row['curso_nome']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Nenhum curso disponível</option>";
                    }
                } catch (PDOException $e) {
                    echo "<option value=''>Erro ao buscar cursos: " . htmlspecialchars($e->getMessage()) . "</option>";
                }
                ?>
            </select>

            <input type="hidden" value="Ativo" id="turmas_status" name="turmas_status">
            <input type="submit" value="Cadastrar Turma">
        </form>
    </div>

    <div class="Pdf-ajuda">
        <a id="" href="/nr12/ajuda/CADASTRO DE TURMA.pdf" target="_blank">Está com dificuldade de
            cadastrar uma turma? Clique aqui.</a>
        <a href="/nr12/ajuda/ALTERAÇÃO DE TURMA.pdf" target="_blank">Cadastrou alguma turma errada e precisa fazer
            alguma alteração? Clique aqui</a>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.querySelector("form");
            const modalToast = document.getElementById("modal-toast");

            form.addEventListener("submit", function (event) {
                event.preventDefault(); // Impede o envio tradicional do formulário

                const formData = new FormData(form);

                fetch('./registro.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json()) // Processa como JSON
                    .then(data => {
                        // Exibe a resposta no modal toast com base no status
                        modalToast.textContent = data.mensagem;
                        modalToast.classList.add("show", data.status);

                        // Limpa o formulário após exibir a mensagem
                        form.reset();

                        setTimeout(() => {
                            modalToast.classList.remove("show", data.status);
                        }, 3000); // Exibe por 3 segundos
                    })
                    .catch(error => {
                        modalToast.textContent = "Erro no envio do formulário.";
                        modalToast.classList.add("show", "erro");

                        // Limpa o formulário mesmo em caso de erro
                        form.reset();

                        setTimeout(() => {
                            modalToast.classList.remove("show", "erro");
                        }, 3000); // Exibe por 3 segundos
                    });
            });
        });
    </script>

</body>

</html>