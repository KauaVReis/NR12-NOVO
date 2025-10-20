<?php
session_start();
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);

// Adiciona uma barra no final se não houver
$base_dir = rtrim($base_dir, '/') . '/';

// Corrige a URL para sempre começar do diretório raiz do projeto
define('BASE_URL', '../../nr12/');
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/nr12/sidebar.php';
// Incluindo a conexão com o banco de dados
include '../conexao.php';



// Armazena seleção ao navegar entre páginas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['requisitos'])) {
    $_SESSION['requisitos_selecionados'] = $_POST['requisitos'];
}

// Limpa a seleção ao enviar o formulário de associação
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['associar_requisitos'])) {
    unset($_SESSION['requisitos_selecionados']);
}

// Busca tipos de máquinas
$stmt_maquinas = $pdo->query("SELECT * FROM tipomaquina WHERE tipomaquina_status = 'Ativo'");
$maquinas = $stmt_maquinas->fetchAll(PDO::FETCH_ASSOC);

// Inicializando a variável de mensagens
$mensagem = '';

// Requisitos disponíveis
$stmt_requisitos = $pdo->query("SELECT * FROM requisitos WHERE requisitos_status = 'Ativo'");
$requisitos_disponiveis = $stmt_requisitos->fetchAll(PDO::FETCH_ASSOC);

// Requisitos associados ao tipo de máquina selecionado
$requisitos_associados = [];
if (isset($_POST['tipomaquina'])) {
    $tipomaquina_id = $_POST['tipomaquina'];

    // Busca requisitos associados ao tipo de máquina selecionado
    $stmt_associados = $pdo->prepare("SELECT r.idrequisitos 
                                       FROM requisitos AS r
                                       JOIN tipomaquina_requisito AS tr ON r.idrequisitos = tr.requisitos_id
                                       WHERE tr.tipomaquina_id = :tipomaquina_id");
    $stmt_associados->bindParam(':tipomaquina_id', $tipomaquina_id);
    $stmt_associados->execute();
    $requisitos_associados = $stmt_associados->fetchAll(PDO::FETCH_COLUMN);
}

// Filtro de tipo_req
$tipo_req_filtro = isset($_POST['tipo_req']) ? $_POST['tipo_req'] : '';

// Buscar requisitos com filtro
$stmt_requisitos = $pdo->prepare("SELECT * FROM requisitos WHERE requisitos_status = 'Ativo'" . ($tipo_req_filtro ? " AND tipo_req = :tipo_req" : ""));

if ($tipo_req_filtro) {
    $stmt_requisitos->bindParam(':tipo_req', $tipo_req_filtro);
}
$stmt_requisitos->execute();
$requisitos = $stmt_requisitos->fetchAll(PDO::FETCH_ASSOC);

// Processar o formulário para associar requisitos a tipos de máquinas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['associar_requisitos'])) {
    $tipomaquina_id = $_POST['tipomaquina'];
    $requisitos_ids = $_POST['requisitos'];

    $mensagens = [];
    foreach ($requisitos_ids as $requisitos_id) {
        // Verificar se a associação já existe
        $sql_verificar = "SELECT COUNT(*) AS count, r.requisito_topico, tm.tipomaquina_nome 
                          FROM tipomaquina_requisito AS tr
                          JOIN requisitos AS r ON tr.requisitos_id = r.idrequisitos
                          JOIN tipomaquina AS tm ON tr.tipomaquina_id = tm.idtipomaquina
                          WHERE tr.requisitos_id = :requisitos_id AND tr.tipomaquina_id = :tipomaquina_id";
        $stmt_verificar = $pdo->prepare($sql_verificar);
        $stmt_verificar->bindParam(':requisitos_id', $requisitos_id);
        $stmt_verificar->bindParam(':tipomaquina_id', $tipomaquina_id);
        $stmt_verificar->execute();

        $resultado = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
        if ($resultado['count'] == 0) {
            $sql_associar = "INSERT INTO tipomaquina_requisito (requisitos_id, tipomaquina_id) VALUES (:requisitos_id, :tipomaquina_id)";
            $stmt_associar = $pdo->prepare($sql_associar);
            $stmt_associar->bindParam(':requisitos_id', $requisitos_id);
            $stmt_associar->bindParam(':tipomaquina_id', $tipomaquina_id);
            $stmt_associar->execute();
        } else {
            $mensagens[] = "O requisito '{$resultado['requisito_topico']}' já está associado ao tipo de máquina '{$resultado['tipomaquina_nome']}'.";
        }
    }

    // Armazena mensagens de feedback
    $_SESSION['mensagens'] = !empty($mensagens) ? $mensagens : ['Processo de associação concluído!'];
    echo "<script>window.location.href = 'tipomaquina_requisito.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Requisitos e Tipos de Máquinas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
            background-color: #cfcfcf;
        }

        h1 {
            color: #black;
            text-align: center;
            margin-bottom: 20px;
        }

        h2 {
            color: #333;
            margin: 15px 0;
        }

        form {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 600px;
            /* Tamanho fixo */
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            margin-top: 10px;
        }

        label {
            display: block;
            margin: 10px 0;
        }

        select,
        input[type="text"],
        button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            /* Garantindo que todos tenham largura total */
            box-sizing: border-box;
            margin-bottom: 10px;
        }

        input[type="checkbox"] {
            margin-right: 10px;
        }

        button {
            background-color: #c0392b;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 16px;
            width: 70%;
            padding-left: 20px;
        }

        button:hover {
            background-color: #a93226;
        }

        /* Mensagens de feedback */
        .mensagens {
            margin: 20px 0;
            color: #e74c3c;
        }

        /* Estilo para a caixa rolável */
        .caixa-requisitos {
            max-height: 295px;
            width: 500px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
            margin-bottom: 20px;
            max-width: 320px;
        }

        /* Responsividade */
        @media (max-width: 600px) {
            form {
                padding: 15px;
                max-width: 345px;
            }

            button {
                padding: 10px 20px;
                font-size: 14px;
            }
        }

        /* Adicionando estilo para a paginação */
        .paginacao {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        .paginacao button {
            padding: 5px 10px;
            margin: 0 5px;
            border: 1px solid #ccc;
            background-color: #c0392b;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }

        .paginacao button:disabled {
            background-color: #ddd;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <h2 id="modal-title">Mensagem</h2>
            <p id="modal-message">Detalhes da mensagem.</p>
            <button class="close-modal" onclick="closeModal()">Fechar</button>
        </div>
    </div>

    <h1 style="margin-top: 25px;">Associar Requisitos a Tipos de Máquinas</h1>

    <form action="" method="post" id="filtroForm">
        <label>Filtrar por tipo de requisito:</label>
        <select name="tipo_req" onchange="this.form.submit()">
            <option value="">Todos</option>
            <option value="Seguranca" <?= $tipo_req_filtro == 'Seguranca' ? 'selected' : '' ?>>Segurança</option>
            <option value="Operacional" <?= $tipo_req_filtro == 'Operacional' ? 'selected' : '' ?>>Operacional</option>
            <option value="Preventivo" <?= $tipo_req_filtro == 'Preventivo' ? 'selected' : '' ?>>Preventivo</option>
        </select>

        <label for="nome_requisito">Pesquisar por nome:</label>
        <input type="text" name="nome_requisito" id="nome_requisito" placeholder="Digite o nome do requisito"
            onkeyup="pesquisarRequisitos()">
    </form>

    <form action="" method="post">
        <label>Tipo de Máquina:</label>
        <select name="tipomaquina" required onchange="this.form.submit()">
            <option value="">Selecione um tipo de máquina</option>
            <?php foreach ($maquinas as $maquina): ?>
                <option value="<?= $maquina['idtipomaquina'] ?>" <?= isset($_POST['tipomaquina']) && $_POST['tipomaquina'] == $maquina['idtipomaquina'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($maquina['tipomaquina_nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <h2>Requisitos:</h2>
        <div class="caixa-requisitos">
            <?php foreach ($requisitos as $requisito): ?>
                <label>
                    <input type="checkbox" name="requisitos[]" value="<?= $requisito['idrequisitos'] ?>"
                        <?= (in_array($requisito['idrequisitos'], $requisitos_associados)) ? 'checked' : '' ?>
                        onchange="atualizarRequisitosSelecionados()">
                    <?= htmlspecialchars($requisito['requisito_topico']) ?>
                </label>
            <?php endforeach; ?>
        </div>

        <div id="requisitosSelecionados">
            <h3>Requisitos Selecionados: <span id="totalRequisitos">0</span></h3>
            <ul id="listaRequisitos"></ul>

        </div>
        </div>

        <button type="submit" name="associar_requisitos">Associar Requisitos</button>

        <!-- Exibir mensagens -->
        <?php if (isset($_SESSION['mensagens'])): ?>
            <div class="mensagens">
                <?php foreach ($_SESSION['mensagens'] as $msg): ?>
                    <p><?= htmlspecialchars($msg) ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['mensagens']); ?>
            </div>
        <?php endif; ?>
    </form>
    <style>

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

    <script>
        const requisitosSelecionadosPorPagina = 15;

        function atualizarRequisitosSelecionados() {
            const checkboxes = document.querySelectorAll('.caixa-requisitos input[type="checkbox"]');
            const lista = document.getElementById('listaRequisitos');
            const totalElement = document.getElementById('totalRequisitos');
            let total = 0;

            // Limpar a lista antes de atualizar
            lista.innerHTML = '';

            checkboxes.forEach((checkbox) => {
                if (checkbox.checked) {
                    total++;
                    const li = document.createElement('li');
                    li.textContent = checkbox.nextSibling.textContent; // Texto do requisito
                    lista.appendChild(li);
                }
            });

            totalElement.textContent = total; // Atualizar o total de requisitos selecionados
        }

        // Chamar a função para atualizar os requisitos inicialmente
        atualizarRequisitosSelecionados();

        function pesquisarRequisitos() {
            const input = document.getElementById('nome_requisito');
            const filter = input.value.toLowerCase();
            const requisitos = document.querySelectorAll('.caixa-requisitos label');

            requisitos.forEach((requisito) => {
                const text = requisito.textContent || requisito.innerText;
                requisito.style.display = text.toLowerCase().includes(filter) ? '' : 'none';
            });

            atualizarRequisitosSelecionados(); // Atualizar requisitos selecionados na pesquisa
        }


        function openModal(title, message) {
            document.getElementById('modal-title').innerText = title;
            document.getElementById('modal-message').innerText = message;
            document.getElementById('modal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }

    </script>

</body>

</html>