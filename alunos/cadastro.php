<?php
session_start();
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');

include __DIR__ . '/../sidebar.php';
include '../conexao.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador', 'Professor']);

// Variável para status de cadastro em lote
$sucessoCadastro = false;


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['arquivo_excel'])) {
        // Lógica para cadastro em lote
        $arquivo = $_FILES['arquivo_excel']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($arquivo);
        $sheet = $spreadsheet->getActiveSheet();

        $mapeamentoColunas = ['matricula' => 0, 'nome' => 1, 'turma' => 2];

        foreach ($sheet->getRowIterator(2) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $dados = [];
            foreach ($cellIterator as $cell) {
                $dados[] = trim($cell->getValue());
            }

            $matricula = $dados[$mapeamentoColunas['matricula']] ?? null;
            $nome = $dados[$mapeamentoColunas['nome']] ?? null;
            $turma = $dados[$mapeamentoColunas['turma']] ?? null;

            // Obtenção do ID da turma ativa
            $stmt_turma = $pdo->prepare("SELECT idturmas FROM turmas WHERE turma_nome = :turma AND turmas_status = 'Ativo'");
            $stmt_turma->bindParam(':turma', $turma, PDO::PARAM_STR);
            $stmt_turma->execute();
            $turma_id = $stmt_turma->fetchColumn();

            if (!$turma_id)
                continue;

            // Verifica se a matrícula já existe
            $stmt_verifica = $pdo->prepare("SELECT COUNT(*) FROM aluno WHERE aluno_matricula = :matricula");
            $stmt_verifica->bindParam(':matricula', $matricula, PDO::PARAM_INT);
            $stmt_verifica->execute();
            $matricula_existe = $stmt_verifica->fetchColumn();

            if ($matricula_existe == 0) {
                // Inserção de dados
                $stmt = $pdo->prepare("INSERT INTO aluno (aluno_nome, aluno_matricula, turmas_id) VALUES (:nome, :matricula, :turma_id)");
                $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
                $stmt->bindParam(':matricula', $matricula, PDO::PARAM_INT);
                $stmt->bindParam(':turma_id', $turma_id, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    $sucessoCadastro = true;
                }
            }
        }

        // Mensagem de sucesso ao cadastrar em lote
        if ($sucessoCadastro) {
            $_SESSION['mensagem'] = "Cadastro em lote realizado com sucesso!";
            $_SESSION['tipo'] = "sucesso";
        }

    } else {
        // Cadastro individual
        $nome = $_POST['nome'] ?? '';
        $matricula = $_POST['matricula'] ?? 0;
        $turma_id = $_POST['turma'];

        try {
            $stmt_verifica = $pdo->prepare("SELECT COUNT(*) FROM aluno WHERE aluno_matricula = :matricula");
            $stmt_verifica->bindParam(':matricula', $matricula, PDO::PARAM_INT);
            $stmt_verifica->execute();
            $matricula_existe = $stmt_verifica->fetchColumn();

            if ($matricula_existe > 0) {
                $_SESSION['mensagem'] = "Erro: Matrícula já cadastrada!";
                $_SESSION['tipo'] = "erro";
            } else {
                $stmt = $pdo->prepare("INSERT INTO aluno (aluno_nome, aluno_matricula, turmas_id) VALUES (:nome, :matricula, :turma_id)");
                $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
                $stmt->bindParam(':matricula', $matricula, PDO::PARAM_INT);
                $stmt->bindParam(':turma_id', $turma_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $_SESSION['mensagem'] = "Dados inseridos com sucesso!";
                    $_SESSION['tipo'] = "sucesso";
                }
            }
        } catch (PDOException $e) {
            $_SESSION['mensagem'] = "Erro: " . $e->getMessage();
            $_SESSION['tipo'] = "erro";
        }
    }
}


// Carrega turmas para o select
try {
    $stmt = $pdo->prepare("SELECT idturmas, turma_nome FROM turmas WHERE turmas_status = 'Ativo'");
    $stmt->execute();
    $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao buscar turmas: " . $e->getMessage();
}
?>


<!-- HTML e CSS para o formulário e modal -->
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserir Dados</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <link rel="icon" type="image/png" href="../imagem/senailogo.png">

    <style>
        body{
            display: flex;
            text-align: center;
            justify-content: center;
        }
        form {
            margin-right: 20px;
            width: 100%;
            padding-top: 40px;
            padding-bottom: 20px;
        }

        /* Estilos do modal */
        .modal {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: #fff;
            padding: 15px;
            border-radius: 5px;
            opacity: 0;
            transition: opacity 0.5s, bottom 0.5s;
            z-index: 1000;
            text-align: center;
        }

        .modal.show {
            display: block;
            opacity: 1;
        }

        .modal.sucesso {
            background-color: #4CAF50;
        }

        .modal.erro {
            background-color: #f44336;
        }

        /* Estilos de navegação */
        .navegacao {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
            /* Espaço entre os botões */
        }

        .navegacao button {
            padding: 12px 24px;
            border: none;
            background-color: #8B0000;
            /* Vermelho escuro */
            color: white;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s, box-shadow 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            /* Sombra suave */
        }

        .navegacao button:hover {
            background-color: #A52A2A;
            /* Tom mais claro de vermelho escuro no hover */
            transform: translateY(-2px);
            /* Eleva o botão levemente no hover */
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.3);
            /* Sombra mais intensa no hover */
        }

        .navegacao button:active {
            background-color: #6A1B1B;
            /* Vermelho ainda mais escuro quando o botão é clicado */
            transform: translateY(1px);
            /* Suaviza o efeito de clique */
        }

        .navegacao button.active {
            background-color: #B22222;
            /* Cor diferente para o botão ativo */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            /* Sombra mais forte para o botão ativo */
        }

        .formulario {
            display: none;
            /* Oculta todos os formulários por padrão */
        }

        .formulario.active {
            display: block;
            /* Exibe o formulário ativo */
        }

        h2 {
            text-align: center;
            margin-bottom: 24px;
            font-size: 1.8rem;
            color: #1f2937;
            text-transform: uppercase;
            letter-spacing: 1.5px;

        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .container_cadastro_aluno {
                display: flex;
                flex-direction: column;

            }

            h2 {
                display: none;
            }

            .navegacao button {
                margin-top: 30px;
                padding: 8px 16px;
            }

            form {
                margin-right: 20px;
                width: 100%;
                padding-top: 40px;
                padding-bottom: 20px;
            }
        }

        @media (max-width: 480px) {
            body{
                height: auto;
            }
            h1 {
                font-size: 29px;
            }
            .Pdf-ajuda {
            width: 85%;
        }
        form{
            width: 90%;
        }
        .container_cadastro_aluno{
            margin-top: 80px;
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
    </style>
</head>

<body>
    <div class="container_cadastro_aluno">
        <h1>CADASTRO DE ALUNOS </h1>

        <!-- Navegação -->
        <div class="navegacao">
            <button id="btnCadastroIndividual" class="active">Cadastro Individual</button>
            <button id="btnCadastroLote">Cadastro em Lote</button>
        </div>

        <!-- Cadastro em Lote -->
        <div id="formCadastroLote" class="formulario">
            <h2 class="lote">Cadastro em Lote</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <label for="arquivo_excel">Selecionar arquivo Excel:</label>
                <input type="file" id="arquivo_excel" name="arquivo_excel" accept=".xlsx" required><br><br>
                <input type="submit" value="Cadastrar em Lote">
            </form>
        </div>

        <!-- Cadastro Individual -->
        <div id="formCadastroIndividual" class="formulario active">
            <h2>Cadastro Individual</h2>
            <form method="POST" action="">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required><br>

                <label for="matricula" required>Matrícula:</label>
                <input type="text" id="matricula" name="matricula" inputmode="numeric" pattern="[0-9]*" maxlength="8"
                    required oninput="this.value = this.value.replace(/[^0-9]/g, '');">


                <label for="turma">Turma:</label>
                <select id="turma" name="turma" required>
                    <option value="">Selecione uma turma</option>
                    <?php
                    foreach ($turmas as $turma) {
                        echo '<option value="' . $turma['idturmas'] . '">' . htmlspecialchars($turma['turma_nome']) . '</option>';
                    }
                    ?>
                </select><br><br>
                <input type="submit" value="Cadastrar Individual">
            </form>
        </div>
    </div>

    <div class="Pdf-ajuda">
        <a id="ajudaLinkIndividual" href="/nr12/ajuda/CADASTRO DE ALUNOS.pdf" target="_blank">Está com dificuldade de
            cadastrar um aluno? Clique aqui.</a>
        <a id="ajudaLinkLote" href="/nr12/ajuda/CADASTRO DE ALUNOS EM LOTE.pdf" target="_blank"
            style="display: none;">Está com dificuldade de cadastrar alunos em lote? Clique aqui.</a>
        <a href="/nr12/ajuda/ALTERAÇÃO DE ALUNO.pdf" target="_blank">Cadastrou algum aluno errado e precisa fazer
            alguma alteração? Clique aqui</a>
    </div>

    <!-- Modal para mensagens -->
    <div id="modal" class="modal"></div>

    <script>
        // Função para atualizar a visibilidade dos links de ajuda
        function atualizarLinksCadastro(cadastroLote) {
            const ajudaLinkIndividual = document.getElementById('ajudaLinkIndividual');
            const ajudaLinkLote = document.getElementById('ajudaLinkLote');

            if (cadastroLote) {
                ajudaLinkIndividual.style.display = 'none';
                ajudaLinkLote.style.display = 'block';
            } else {
                ajudaLinkIndividual.style.display = 'block';
                ajudaLinkLote.style.display = 'none';
            }
        }

        // Event listeners para alternar os formulários e atualizar os links
        document.getElementById('btnCadastroIndividual').addEventListener('click', function () {
            document.getElementById('formCadastroIndividual').classList.add('active');
            document.getElementById('formCadastroLote').classList.remove('active');
            this.classList.add('active');
            document.getElementById('btnCadastroLote').classList.remove('active');
            atualizarLinksCadastro(false); // Cadastro individual
        });

        document.getElementById('btnCadastroLote').addEventListener('click', function () {
            document.getElementById('formCadastroLote').classList.add('active');
            document.getElementById('formCadastroIndividual').classList.remove('active');
            this.classList.add('active');
            document.getElementById('btnCadastroIndividual').classList.remove('active');
            atualizarLinksCadastro(true); // Cadastro em lote
        });

        // Exibe mensagem de sucesso ou erro se houver
        <?php if (isset($_SESSION['mensagem'])): ?>
            const modal = document.getElementById('modal');
            modal.classList.add('<?php echo $_SESSION['tipo']; ?>', 'show');
            modal.innerHTML = "<?php echo addslashes($_SESSION['mensagem']); ?>";
            setTimeout(() => {
                modal.classList.remove('show');
            }, 3000);
            <?php unset($_SESSION['mensagem']); ?>
        <?php endif; ?>
    </script>
</body>

</html>