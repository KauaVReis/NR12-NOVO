<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);

// Adiciona uma barra no final se não houver
$base_dir = rtrim($base_dir, '/') . '/';

// Corrige a URL para sempre começar do diretório raiz do projeto
// define('BASE_URL', '../../nr12/');

if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    $mensagem_tipo = $_SESSION['mensagem_tipo'];
    unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']);
}
?>
<?php include __DIR__ . '/../sidebar.php'; ?>

<?php

// Incluindo a conexão com o banco de dados
include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador', 'Professor']);


try {
    // Definindo o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscando tipos de máquinas
    $tipomaquinas = $pdo->query("SELECT idtipomaquina, tipomaquina_nome FROM tipomaquina")->fetchAll(PDO::FETCH_ASSOC);

    // Buscando todos os requisitos para exibir no formulário
    $requisitosTodos = $pdo->query("SELECT idrequisitos, requisito_topico FROM requisitos")->fetchAll(PDO::FETCH_ASSOC);

    // Buscando setores
    $setores = $pdo->query("SELECT idsetor, setor_nome FROM setor")->fetchAll(PDO::FETCH_ASSOC);

    // Buscando motores
    $motores = $pdo->query("SELECT idmotor, motor_fabricante, motor_modelo, motor_potencia, motor_tensão, motor_corrente FROM motor")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro na consulta: " . $e->getMessage());
}


// Verifica se o tipo de máquina foi selecionado
$requisitos = [];
if (isset($_GET['tipomaquina_id'])) {
    $tipomaquina_id = $_GET['tipomaquina_id'];

    // Buscando os requisitos correspondentes
    $sql = "
        SELECT r.idrequisitos, r.requisito_topico
        FROM tipomaquina_requisito tr
        JOIN requisitos r ON tr.requisitos_id = r.idrequisitos
        WHERE tr.tipomaquina_id = :tipomaquina_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':tipomaquina_id', $tipomaquina_id);
    $stmt->execute();
    $requisitos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Adicionando nova máquina
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['maquina_ni'], $_POST['tipomaquina_id'], $_POST['setor_id'])) {
    $maquina_nis = explode(',', $_POST['maquina_ni']); // Divide a string de NIs em um array
    $tipomaquina_id = $_POST['tipomaquina_id'];
    $setor_id = $_POST['setor_id'];
    $data_criacao = date("Y-m-d");
    $intervalo_manutencao = $_POST['intervalo_manutencao'];

    // Cálculo da data da próxima manutenção (fora do loop)
    $meses = 0;
    switch ($intervalo_manutencao) {
        case '3':
            $meses = 3;
            break;
        case '6':
            $meses = 6;
            break;
        case '12':
            $meses = 12;
            break;
    }

    // ... (capturando outros campos - fora do loop)
    $maquina_peso = $_POST['maquina_peso'];
    $maquina_fabricante = $_POST['maquina_fabricante'];
    $maquina_modelo = $_POST['maquina_modelo'];
    $maquina_ano = $_POST['maquina_ano'];
    $maquina_capacidade = $_POST['maquina_capacidade'];
    $motor_id = $_POST['motor_id']; // Captura o motor_id


    foreach ($maquina_nis as $maquina_ni) {
        $maquina_ni = trim($maquina_ni);

        if (!empty($maquina_ni)) {
            // Verifica se o NI já existe no banco de dados
            $stmtCheck = $pdo->prepare("SELECT maquina_ni FROM maquina WHERE maquina_ni = :maquina_ni");
            $stmtCheck->bindParam(':maquina_ni', $maquina_ni);
            $stmtCheck->execute();

            if ($stmtCheck->rowCount() > 0) {
                // NI já cadastrado, exibe o modal e interrompe o loop
                echo "<script>
                        alert('O NI " . $maquina_ni . " já está cadastrado!');
                      </script>"; 
                echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "?tipomaquina_id=" . $tipomaquina_id . "';</script>";
                continue; // Pula para o próximo NI
            }


            $data_proxima_manutencao = ($intervalo_manutencao) ? date('Y-m-d', strtotime("+$meses months", strtotime($data_criacao))) : null;
            try {

                $sqlInsert = "INSERT INTO maquina (maquina_ni, tipomaquina_id, setor_id, maquina_peso, maquina_fabricante, maquina_modelo, maquina_ano, maquina_capacidade, data_criacao, data_proxima_manutencao, intervalo_manutencao, motor_id) 
                            VALUES (:maquina_ni, :tipomaquina_id, :setor_id, :maquina_peso, :maquina_fabricante, :maquina_modelo, :maquina_ano, :maquina_capacidade, :data_criacao, :data_proxima_manutencao, :intervalo_manutencao, :motor_id)";

                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->bindParam(':maquina_ni', $maquina_ni);
                $stmtInsert->bindParam(':tipomaquina_id', $tipomaquina_id);
                $stmtInsert->bindParam(':setor_id', $setor_id);
                $stmtInsert->bindParam(':maquina_peso', $maquina_peso);
                $stmtInsert->bindParam(':maquina_fabricante', $maquina_fabricante);
                $stmtInsert->bindParam(':maquina_modelo', $maquina_modelo);
                $stmtInsert->bindParam(':maquina_ano', $maquina_ano);
                $stmtInsert->bindParam(':maquina_capacidade', $maquina_capacidade);
                $stmtInsert->bindParam(':data_criacao', $data_criacao);
                $stmtInsert->bindParam(':data_proxima_manutencao', $data_proxima_manutencao);
                $stmtInsert->bindParam(':intervalo_manutencao', $intervalo_manutencao);
                $stmtInsert->bindParam(':motor_id', $motor_id);
                $stmtInsert->execute();
                $maquina_id = $pdo->lastInsertId();


                // ... (inserir requisitos específicos usando $maquina_id do loop atual)
                if (isset($_POST['requisitos'])) {  // Verifique se há requisitos
                    $requisitosAdicionais = explode(',', $_POST['requisitos']);
                    inserirRequisitosEspecificos($pdo, $maquina_id, $requisitosAdicionais);
                }


            } catch (PDOException $e) {
                echo "Erro ao inserir máquina: " . $e->getMessage();
            }
        }
    }

    echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "?tipomaquina_id=" . $tipomaquina_id . "';</script>";
    exit;
}



// Função para inserir requisitos específicos (fora do loop principal)
function inserirRequisitosEspecificos($pdo, $maquina_id, $requisitosAdicionais)
{
    foreach ($requisitosAdicionais as $requisito) {
        $requisito = trim($requisito);
        if ($requisito) {
            try {
                $sqlInsertRequisito = "INSERT INTO maquina_requisitos (maquina_id, requisitos_especificos) 
                                        VALUES (:maquina_id, :requisitos_especificos)";
                $stmtInsertRequisito = $pdo->prepare($sqlInsertRequisito);
                $stmtInsertRequisito->bindParam(':maquina_id', $maquina_id);
                $stmtInsertRequisito->bindParam(':requisitos_especificos', $requisito);
                $stmtInsertRequisito->execute();

            } catch (PDOException $e) {
                // Lidar com o erro, talvez exibir uma mensagem ou logar o erro.
                echo "Erro ao inserir requisito específico: " . $e->getMessage();
            }
        }
    }
}

?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<style>
    select{
        width: 80% !important; 
    }
    body {
        font-family: Arial, sans-serif;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 0;
        padding: 0;
        color: #1f2937;
    }

    h1 {
        text-align: center;
        color: #1f2937;
        margin-top: 20px;
    }

    .container_maq {
        width: 100%;
        max-width: 1200px;
        margin: 20px 0;
    }

    .mensagem-sucesso {
        background-color: #d4edda;
        color: #155724;
        padding: 10px;
        margin-top: 10px;
        border: 1px solid #c3e6cb;
        border-radius: 5px;
    }

    .form_req_maq {
        display: flex;
        justify-content: center;
        width: 100%;
    }

    .form_req_maq_reqsito {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 10px;
    }

    .tabelas {
        max-height: 300px;
        overflow-y: auto;
        width: 100%;
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        padding: 10px;
    }

    .th_titulo {
        background-color: #e21616;
        color: #ffffff;
        padding: 10px;
        text-transform: uppercase;
        font-size: 1rem;
        font-weight: bold;
        letter-spacing: 1px;
        border-bottom: 3px solid #b30000;
    }

    .container-forms {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        width: 100%;
        justify-content: center;
    }

    form {
        width: 100%;
        max-width: 500px;
        background-color: #ffffff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border: 1px solid #ddd;
    }

    input[type="text"],
    input[type="number"],
    input[type="email"],
    select {
        width: 100%;
        padding: 10px;
        margin: 8px 0;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        font-size: 1rem;
        background-color: #f9fafb;
        color: #1f2937;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    input[type="text"]:focus,
    select:focus {
        border-color: #e21616;
        box-shadow: 0 4px 8px rgba(79, 70, 229, 0.1);
        outline: none;
    }

    button[type="button"],
    input[type="submit"] {
        background-color: #e21616;
        color: #ffffff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
        text-transform: uppercase;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        margin: 10px auto;
        transition: all 0.3s ease;
        display: block;
    }

    button[type="button"]:hover,
    input[type="submit"]:hover {
        background-color: #fd2020;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        transform: translateY(-3px);
    }

    .btn-adicionar {
        background-color: #e21616;
        color: #ffffff;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        text-transform: uppercase;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-adicionar:hover {
        background-color: #fd2020;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        transform: translateY(-3px);
    }

    h2 {
        margin-bottom: 30px;
        font-size: 1.2rem;
        color: #1f2937;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        text-align: center;
    }

    label {
        font-weight: bold;
        color: #4b5563;
        font-size: 0.9rem;
        margin-top: 10px;
        display: block;
    }

    .icone-adicao {
        width: 20px;
        height: 20px;
        margin-right: 8px;
    }

    .container_cadastro_maq {
        padding-top: 70px;
        text-align: center;
    }

    @media (max-width: 540px) {
        .container_maq {
            width: 100%;
            width: 300px;
            max-width: 500px;
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
<style>

.modal-conteudo {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #28a745; /* Cor de fundo verde */
    color: white; /* Texto branco */
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    font-size: 16px;
    font-family: Arial, sans-serif;
    z-index: 1000;
    text-align: center;
    animation: fadeIn 0.5s ease, fadeOut 0.5s ease 2.5s;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}

</style>
<body>
    <div class="container_cadastro_maq">
        <h1>Cadastro de Máquina</h1>
        <div class="container-forms">
            <div class="forms">
                <form method="POST" onsubmit="return exibirModalSucesso()" class="container_maq">
                    <label for="tipomaquina">Tipo de Máquina:</label>
                    <select id="tipomaquina" name="tipomaquina_id" onchange="buscarRequisitos()" required>
                        <option value="">Selecione</option>
                        <?php foreach ($tipomaquinas as $tipomaquina): ?>
                            <option value="<?= htmlspecialchars($tipomaquina['idtipomaquina']) ?>"
                                <?= isset($_GET['tipomaquina_id']) && $_GET['tipomaquina_id'] == $tipomaquina['idtipomaquina'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipomaquina['tipomaquina_nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="intervalo_manutencao">Intervalo de Manutenção (meses):</label>
                    <select id="intervalo_manutencao" name="intervalo_manutencao" required>
                        <option value="">Selecione</option>
                        <option value="3">3 Meses</option>
                        <option value="6">6 Meses</option>
                        <option value="12">1 Ano (12 meses)</option>
                    </select>

                    <label for="setor">Setor:</label>
                    <select id="setor" name="setor_id" required>
                        <option value="">Selecione</option>
                        <?php foreach ($setores as $setor): ?>
                            <option value="<?= htmlspecialchars($setor['idsetor']) ?>">
                                <?= htmlspecialchars($setor['setor_nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="motor">Motor:</label>
                    <select id="motor" name="motor_id" required>
                        <option value="">Selecione</option>
                        <?php foreach ($motores as $motor): ?>
                            <option value="<?= htmlspecialchars($motor['idmotor']) ?>">
                                <?= htmlspecialchars(
                                    ($motor['motor_fabricante'] ?: 'FABRICANTE NÃO ESPECIFICADO') . ' - ' .
                                    ($motor['motor_modelo'] ?: 'MODELO NÃO ESPECIFICADO') . ' - ' .
                                    ($motor['motor_potencia'] ?: 'POTÊNCIA NÃO ESPECIFICADA') . ' - ' .
                                    ($motor['motor_tensão'] ?: 'TENSÃO NÃO ESPECIFICADA') . ' - ' .
                                    ($motor['motor_corrente'] ?: 'CORRENTE NÃO ESPECIFICADA'),
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="maquina_ni">NI da Máquina (separados por vírgula):</label>
                    <input type="text" id="maquina_ni" name="maquina_ni" required placeholder="NI1, NI2, NI3, ...">

                    <label for="maquina_peso">Peso da Máquina:</label>
                    <input type="text" id="maquina_peso" name="maquina_peso" required>
                    <label for="maquina_fabricante">Fabricante:</label>
                    <input type="text" id="maquina_fabricante" name="maquina_fabricante" required>
                    <label for="maquina_modelo">Modelo:</label>
                    <input type="text" id="maquina_modelo" name="maquina_modelo" required>
                    <label for="maquina_ano">Ano de Fabricação:</label>
                    <input type="number" id="maquina_ano" name="maquina_ano" required>
                    <label for="maquina_capacidade">Capacidade:</label>
                    <input type="text" id="maquina_capacidade" name="maquina_capacidade" required>

                    <label for="requisitos" class="label-requisitos">Requisitos Adicionais:</label>
                    <ul id="lista_requisitos"></ul>

                    <div id="form-requisito" style="display:none; margin-top: 10px;">
                        <select id="tipo_requisito" name="tipo_requisito">
                            <option value="">Selecione o tipo de requisito</option>
                            <option value="Operacional">Operacional</option>
                            <option value="Segurança">Segurança</option>
                            <option value="Preventivo">Preventivo</option>
                        </select>
                        <input type="text" id="requisito_especifico" name="requisito_especifico"
                            placeholder="Especificar requisito">
                        <button type="button" onclick="adicionarRequisito()">Adicionar</button>
                    </div>
                    <input type="hidden" id="requisitos" name="requisitos">
                    <button type="button" class="btn-adicionar"
                        onclick="document.getElementById('form-requisito').style.display='block'">
                        Adicionar Requisito
                    </button>

                    <input type="submit" value="Cadastrar Máquina" class="btn-enviar">
                </form>
            </div>
        </div>

        <!-- Modal de Sucesso -->
        <div id="modalSucesso" class="modal-sucesso" style="display: none;">
            <div class="modal-conteudo">
                <p>Máquina cadastrada com sucesso!</p>
            </div>
        </div>

        <div class="Pdf-ajuda">
            <a href="../ajuda/CADASTRO DE MÁQUINA.pdf" target="_blank">Está com dificuldade de cadastrar uma
                Máquina? Clique aqui.</a><br>
            <a href="../ajuda/ALTERAÇÃO DE MÁQUINAS.pdf" target="_blank">Cadastrou alguma máquina errada e
                precisa fazer alguma alteração? Clique aqui</a>
        </div>
    </div>

    <script>
        function exibirModalSucesso() {
            const modal = document.getElementById('modalSucesso');
            modal.style.display = 'block';

            setTimeout(() => {
                modal.style.display = 'none';
            }, 3000); // Oculta o modal após 3 segundos

            return false; // Evita o envio real do formulário para demonstração
        }
    </script>
</body>

