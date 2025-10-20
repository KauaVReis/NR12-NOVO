<?php
session_start();
include '../conexao.php';

$matricula = $_SESSION['matricula'];
$nimaquina = $_SESSION['nimaquina'];

$mensagem = '';
$maquina_id = null;
$requisitos = [];
$colaboradores = [];
$aluno_id = null;
$requisitos_especificos = [];

try {
    // Busca o idmaquina e tipomaquina_id correspondentes ao maquina_ni na sessão
    $sqlMaquina = "SELECT idmaquina, tipomaquina_id FROM maquina WHERE maquina_ni = :maquina_ni";
    $stmtMaquina = $pdo->prepare($sqlMaquina);
    $stmtMaquina->bindParam(':maquina_ni', $nimaquina);
    $stmtMaquina->execute();
    $maquina = $stmtMaquina->fetch(PDO::FETCH_ASSOC);

    if ($maquina) {
        $maquina_id = $maquina['idmaquina'];
        $tipomaquina_id = $maquina['tipomaquina_id'];

        // Consulta para os requisitos de Seguranca associados ao tipo da máquina
        $sqlRequisitos = "
            SELECT r.idrequisitos, r.requisito_topico
            FROM tipomaquina_requisito tr
            JOIN requisitos r ON tr.requisitos_id = r.idrequisitos
            WHERE tr.tipomaquina_id = :tipomaquina_id AND r.tipo_req = 'Seguranca'
        ";
        $stmtRequisitos = $pdo->prepare($sqlRequisitos);
        $stmtRequisitos->bindParam(':tipomaquina_id', $tipomaquina_id);
        $stmtRequisitos->execute();
        $requisitos = $stmtRequisitos->fetchAll(PDO::FETCH_ASSOC);

        // Busca os requisitos específicos da máquina
        $sqlRequisitosEspecificos = "
            SELECT idmaquina_requisitos, requisitos_especificos
            FROM maquina_requisitos
            WHERE maquina_id = :maquina_id
        ";
        $stmtRequisitosEspecificos = $pdo->prepare($sqlRequisitosEspecificos);
        $stmtRequisitosEspecificos->bindParam(':maquina_id', $maquina_id);
        $stmtRequisitosEspecificos->execute();
        $requisitos_especificos = $stmtRequisitosEspecificos->fetchAll(PDO::FETCH_ASSOC);
    } else {
        echo "Nenhuma máquina encontrada com esse NI.";
    }

    // Busca os colaboradores e seus emails diretamente da tabela colaborador
    $sqlColaboradores = "
        SELECT idcolaborador, colaborador_nome, colaborador_email 
        FROM colaborador
        WHERE colaborador_status = 'Ativo'  -- Somente colaboradores ativos
    ";
    $stmtColaboradores = $pdo->prepare($sqlColaboradores);
    $stmtColaboradores->execute();
    $colaboradores = $stmtColaboradores->fetchAll(PDO::FETCH_ASSOC);

    // Busca o ID do aluno com base na matrícula
    $sqlAluno = "
  SELECT a.idaluno, a.aluno_nome, t.idturmas, t.colaborador_id
  FROM aluno a
  JOIN turmas t ON a.turmas_id = t.idturmas
  WHERE a.aluno_matricula = :matricula
";
    $stmtAluno = $pdo->prepare($sqlAluno);
    $stmtAluno->bindParam(':matricula', $matricula);
    $stmtAluno->execute();
    $aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);

    if ($aluno) {
        $aluno_id = $aluno['idaluno'];
        $aluno_nome = $aluno['aluno_nome'];
        $id_turma = $aluno['idturmas'];
        $colaborador_id = $aluno['colaborador_id'];

        // Agora, busca o colaborador diretamente associado à turma
        $sqlColaboradores = "
    SELECT c.idcolaborador, c.colaborador_nome, c.colaborador_email 
    FROM colaborador c
    JOIN turmas t ON t.colaborador_id = c.idcolaborador
    WHERE t.idturmas = :id_turma AND c.colaborador_status = 'Ativo'
";
        $stmtColaboradores = $pdo->prepare($sqlColaboradores);
        $stmtColaboradores->bindParam(':id_turma', $id_turma);
        $stmtColaboradores->execute();
        $colaboradores = $stmtColaboradores->fetchAll(PDO::FETCH_ASSOC);
    } else {
        echo "Matrícula não encontrada.";
    }
} catch (PDOException $e) {
    echo "Erro ao buscar dados: " . $e->getMessage();
}

// Lógica de inserção no histórico
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    date_default_timezone_set('America/Sao_Paulo');
    $colaborador_id = $_POST['colaborador_id'];
    $requisitos_ids = $_POST['requisitos_ids'] ?? [];
    $requisitos_especifico_ids = $_POST['requisitos_especifico_ids'] ?? [];
    $historico_data = date("Y-m-d H:i:s");
    $historico_hora = $historico_data;

    try {
        if (!empty($requisitos_ids) || !empty($requisitos_especifico_ids)) {
            foreach ($requisitos_ids as $requisitos_id) {
                $sql = "INSERT INTO historico (maquina_id, aluno_id, colaborador_id, requisito_id, historico_data, historico_hora)
                        VALUES (:maquina_id, :aluno_id, :colaborador_id, :requisitos_id, :historico_data, :historico_hora)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':maquina_id', $maquina_id);
                $stmt->bindParam(':aluno_id', $aluno_id);
                $stmt->bindParam(':colaborador_id', $colaborador_id);
                $stmt->bindParam(':requisitos_id', $requisitos_id);
                $stmt->bindParam(':historico_data', $historico_data);
                $stmt->bindParam(':historico_hora', $historico_hora);
                $stmt->execute();
            }
            foreach ($requisitos_especifico_ids as $requisitos_especifico_id) {
                $sqlVerificacao = "SELECT COUNT(*) FROM maquina_requisitos WHERE idmaquina_requisitos = :requisitos_especifico_id";
                $stmtVerificacao = $pdo->prepare($sqlVerificacao);
                $stmtVerificacao->bindParam(':requisitos_especifico_id', $requisitos_especifico_id);
                $stmtVerificacao->execute();

                if ($stmtVerificacao->fetchColumn() > 0) {
                    $sql = "INSERT INTO historico (maquina_id, aluno_id, colaborador_id, requisito_especifico_id, historico_data, historico_hora)
                            VALUES (:maquina_id, :aluno_id, :colaborador_id, :requisitos_especifico_id, :historico_data, :historico_hora)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':maquina_id', $maquina_id);
                    $stmt->bindParam(':aluno_id', $aluno_id);
                    $stmt->bindParam(':colaborador_id', $colaborador_id);
                    $stmt->bindParam(':requisitos_especifico_id', $requisitos_especifico_id);
                    $stmt->bindParam(':historico_data', $historico_data);
                    $stmt->bindParam(':historico_hora', $historico_hora);
                    $stmt->execute();
                } else {
                    $mensagem = "O requisito específico com ID $requisitos_especifico_id não existe.";
                }
            }
            $mensagem = "Checklist feito com sucesso!";

            // Define uma variável de sessão para indicar sucesso
            $_SESSION['checklist_sucesso'] = true;

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $mensagem = "Nenhum requisito selecionado ou matrícula/máquina inválida.";
        }
    } catch (PDOException $e) {
        $mensagem = "Erro ao cadastrar histórico: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilos.css">
    <title>Checklist Segurança</title>
</head>

<body class="body-check-operacional">
    <?php if ($mensagem): ?>
        <div id="toast" class="toast"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <div id="toastRequire" class="toastRequire">Selecione todos os requisitos!!</div>

    <h1 class="checkoperacional">Checklist Segurança</h1>

    <div class="container-botoes">
        <button class="boton-elegante" onclick="voltar()">Voltar</button>
        <button id="openModal" class="abrirModal">Reportar</button>
    </div>

    <!-- Modal de sucesso -->
    <div id="successModal" class="modalCheckoperacional" style="display: none;">
        <div class="modal-content-operacional">
            <h2>Checklist Enviado com Sucesso!</h2>
            <p>Seu checklist foi registrado com sucesso.</p>
            <button id="closeSuccessModal">Fechar</button>
        </div>
    </div>

    <!-- Modal com informações do aluno e da máquina -->
    <div id="myModal" class="modalCheckoperacional">
        <div class="modal-content-operacional">
            <span class="close" id="closeModal">&times;</span>
            <h2>Informações do Aluno e da Máquina</h2>
            <p><strong>Matricula:</strong> <?= htmlspecialchars($matricula) ?></p>
            <p><strong>Nome do Aluno:</strong> <?= isset($aluno_nome) ? htmlspecialchars($aluno_nome) : 'N/A' ?></p>
            <p><strong>NI da Máquina:</strong> <?= htmlspecialchars($nimaquina) ?></p>
            <p><strong>ID da Máquina:</strong> <?= htmlspecialchars($maquina_id ?? 'N/A') ?></p>



            <form id="formRegistro" action="registrar_defeito.php" method="post">
                <h3 class="titulochecks">Possível Erro da Máquina:</h3>
                <textarea name="descricao" rows="4" cols="50" placeholder="Descreva o erro aqui..." required></textarea>

                <h3 class="titulochecks">Colaborador Responsável</h3>
                <label for="colaborador_id_modal" class="colaboradorModal">COLABORADOR</label>
                <select name="colaborador_id_modal" id="colaborador_id_modal" class="selectColaboradorModal" required>
                    <option value="">Selecione um colaborador</option>
                    <?php foreach ($colaboradores as $colaborador): ?>
                        <option value="<?= htmlspecialchars($colaborador['idcolaborador']) ?>">
                            <?= htmlspecialchars($colaborador['colaborador_nome']) ?>
                            (<?= htmlspecialchars($colaborador['colaborador_email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="hidden" name="aluno_id" value="<?= htmlspecialchars($aluno_id) ?>">
                <input type="hidden" name="maquina_id" value="<?= htmlspecialchars($maquina_id) ?>">

                <div id="modalUncheckedRequirements"> </div>

                <div>
                    <div id="modalUncheckedRequirements" style="display: none;">
                        <?php foreach ($requisitos as $requisito): ?>
                            <?php if (!in_array($requisito['idrequisitos'], $_POST['requisitos_ids'] ?? [])): ?>
                                <input type="hidden" name="requisitos_ids[]"
                                    value="<?= htmlspecialchars($requisito['idrequisitos']) ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" id="btnRegistrar">Registrar</button>
                </div>
            </form>
        </div>
    </div>

    <form method="post" id="mainForm" require>
        <div class="colab">
            <label for="colaborador_id" class="colaborador">COLABORADOR</label>
            <select name="colaborador_id" id="colaborador_id_modal" class="selectColaboradorModal" required>
                <option value="">Selecione um colaborador</option>
                <?php foreach ($colaboradores as $colaborador): ?>
                    <option value="<?= htmlspecialchars($colaborador['idcolaborador']) ?>">
                        <?= htmlspecialchars($colaborador['colaborador_nome']) ?>
                        (<?= htmlspecialchars($colaborador['colaborador_email']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Requisitos Operacional -->
        <!-- Requisitos Operacional -->
        <h3 class="titulochecks">Requisitos de segurança</h3>
        <?php foreach ($requisitos as $requisito): ?>
            <label class="checkbox">
                <input class="checkbox-input main-checkbox" type="checkbox" required name="requisitos_ids[]"
                    value="<?= htmlspecialchars($requisito['idrequisitos']) ?>" data-tipo="padrao" />
                <!-- Adicione o data-tipo como "padrao" -->
                <svg required class="checkbox-check" width="25" height="25">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <?= htmlspecialchars($requisito['requisito_topico']) ?>
            </label><br>
        <?php endforeach; ?>

        <!-- Requisitos Específicos -->
        <?php if ($requisitos_especificos): ?>
            <div class="requisitos-especificos">
                <h2>Requisitos Específicos:</h2>
                <ul>
                    <?php foreach ($requisitos_especificos as $requisito): ?>
                        <li>
                            <label class="checkbox">
                                <input required class="checkbox-input main-checkbox" type="checkbox"
                                    name="requisitos_especifico_ids[]"
                                    value="<?= htmlspecialchars($requisito['idmaquina_requisitos']) ?>"
                                    data-tipo="especifico" /> <!-- Adicione o data-tipo como "especifico" -->
                                <svg required class="checkbox-check" width="28" height="28">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <?= htmlspecialchars($requisito['requisitos_especificos']) ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            
        <?php endif; ?>

        <div class="botao">
            <button type="submit" class="cadastrarhist">Enviar</button>
        </div>
    </form>




    <script>
        document.getElementById("openModal").onclick = function () {
            document.getElementById("myModal").style.display = "block";
            const modalUncheckedRequirements = document.getElementById("modalUncheckedRequirements");
            modalUncheckedRequirements.innerHTML = "<h3 class='titulochecks'>Requisitos fora da norma</h3>";

            let requisitosNaoChecadosPadrão = [];
            let requisitosNaoChecadosEspecificos = [];

            // Iterar sobre todos os checkboxes do formulário
            document.querySelectorAll('#mainForm input[type="checkbox"]').forEach(checkbox => {
                if (!checkbox.checked) {
                    const requisitoLabel = checkbox.parentElement.textContent.trim();
                    const requisitoId = checkbox.value;

                    const label = document.createElement('label');
                    label.classList.add('checkbox');
                    label.innerHTML = ` 
            <input type="checkbox" checked class="checkbox-input" value="${requisitoId}" disabled>
            <svg class="checkbox-check" width="28" height="28">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            ${requisitoLabel}
        `;

                    // Verificar se é requisito específico ou padrão
                    if (checkbox.dataset.tipo === 'especifico') {
                        requisitosNaoChecadosEspecificos.push(requisitoId);
                    } else {
                        requisitosNaoChecadosPadrão.push(requisitoId);
                    }

                    modalUncheckedRequirements.appendChild(label);
                }
            });

            // Log para verificar os dois arrays separados
            console.log("Requisitos não checados Padrão:", requisitosNaoChecadosPadrão);
            console.log("Requisitos não checados Específicos:", requisitosNaoChecadosEspecificos);

            // Adicionar os requisitos não checados ao formulário escondido
            if (requisitosNaoChecadosPadrão.length > 0) {
                const inputNaoChecados = document.createElement('input');
                inputNaoChecados.type = 'hidden';
                inputNaoChecados.name = 'requisitos_nao_checados[]';
                inputNaoChecados.value = requisitosNaoChecadosPadrão.join(',');
                modalUncheckedRequirements.appendChild(inputNaoChecados);
            }

            if (requisitosNaoChecadosEspecificos.length > 0) {
                const inputNaoChecadosEspecificos = document.createElement('input');
                inputNaoChecadosEspecificos.type = 'hidden';
                inputNaoChecadosEspecificos.name = 'requisitos_nao_checados_especificos[]';
                inputNaoChecadosEspecificos.value = requisitosNaoChecadosEspecificos.join(',');
                modalUncheckedRequirements.appendChild(inputNaoChecadosEspecificos);
            }
        };


        // Fecha o modal ao clicar fora da área de conteúdo
        window.addEventListener('click', function(event) {
            const myModal = document.getElementById("myModal");
            if (event.target == myModal) {
                myModal.style.display = "none";
            }
        });


        // Função para exibir o toast de notificação
        function showToast() {
            const toast = document.getElementById("toastRequire");
            toast.classList.add("show");

            // Ocultar o toast após 3 segundos
            setTimeout(() => {
                toast.classList.remove("show");
            }, 3000);
        }

        // Exibe mensagem de sucesso quando o formulário é submetido
        <?php if ($mensagem): ?>
            const toast = document.getElementById("toast");
            toast.classList.add("show");
            setTimeout(() => {
                toast.classList.remove("show");
            }, 3000);
        <?php endif; ?>

        function voltar() {
            window.location.href = 'menualuno.php'
        }
        // Função para exibir o modal de sucesso
        // Função para exibir o modal de sucesso
        function showSuccessModal() {
            const successModal = document.getElementById("successModal");
            successModal.style.display = "block";
        }

        // Exibe o modal de sucesso quando o formulário é submetido com sucesso
        <?php if ($mensagem === "Checklist feito com sucesso!"): ?>
            showSuccessModal();
        <?php endif; ?>
        // Fecha o modal de sucesso
        document.getElementById("closeSuccessModal").onclick = function () {
            document.getElementById("successModal").style.display = "none";
        };

        // Fecha o modal ao clicar fora
        window.onclick = function (event) {
            if (event.target == document.getElementById("successModal")) {
                document.getElementById("successModal").style.display = "none";
            }
        };

        // Função para exibir o modal de sucesso (modificada)
        function showSuccessModal() {
            const successModal = document.getElementById("successModal");
            successModal.style.display = "block";
            setTimeout(() => {
                successModal.style.opacity = 1; // Define a opacidade para 1 após o display block
            }, 10); // Pequeno atraso para garantir que o display: block tenha efeito antes de aplicar a opacidade
        }

        // Fecha o modal de sucesso e redireciona (modificado)
        document.getElementById("closeSuccessModal").onclick = function () {
            const successModal = document.getElementById("successModal");
            successModal.style.opacity = 0; // Define a opacidade para 0 antes de ocultar o modal
            setTimeout(() => {
                successModal.style.display = "none";
                window.location.href = 'menualuno.php'; // Redireciona após o modal fechar
            }, 300); // Aguarda o tempo da transição de opacidade
        };



        // Exibe o modal de sucesso quando o formulário é submetido com sucesso
        <?php if (isset($_SESSION['checklist_sucesso']) && $_SESSION['checklist_sucesso']): ?>
            showSuccessModal();
            <?php unset($_SESSION['checklist_sucesso']); ?> // Limpa a variável de sessão
        <?php endif; ?>
    </script>
</body>
<style>
    .colab {
        display: flex;
        text-align: center;
        flex-direction: column;
        align-items: center;
    }

    form {
        align-items: normal;
    }

    .botao {
        justify-content: center;
        align-items: center;
        display: flex;
    }

    h2 {
        text-align: center;
    }

    .modalCheckoperacional {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }

    textarea {
        width: 100%;
        min-height: 80px;
        max-height: 200px;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 5px;
        resize: none;
        font-size: 14px;
        line-height: 1.4;
        transition: border-color 0.3s ease;
    }

    .modal-content-operacional {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    /* Estilos para o modal de sucesso */
    #successModal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
        /* Fundo escurecido */
        transition: opacity 0.3s ease-in-out;
        align-items: center;
        /* Centraliza verticalmente */
        justify-content: center;
        /* Centraliza horizontalmente */
    }

    .modal-content-operacional {
        background-color: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        text-align: center;
        max-width: 400px;
        /* Largura máxima */
        width: 90%;
        /* Largura responsiva */
        position: relative;
        /* Para posicionar o botão de fechar */
        animation: slide-in 0.3s ease-in-out;
        /* Animação de entrada */
    }

    .modal-content-operacional h2 {
        color: #333;
        /* Cor do título */
        margin-bottom: 20px;
    }

    .modal-content-operacional p {
        color: #666;
        /* Cor do texto */
        margin-bottom: 20px;
    }


    #closeSuccessModal {
        background-color: #B22222;
        /* Cor de fundo do botão */
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s ease;
        /* Transição suave na cor de fundo */
        position: absolute;
        /* Posicionamento absoluto para ficar fixo */
        bottom: 10px;
        /* Distância da parte inferior */
        left: 50%;
        /* Centralizar horizontalmente */
        transform: translateX(-50%);
        /* Centralizar horizontalmente */
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
        /* Sombra */
    }

    #closeSuccessModal:hover {
        background-color: #dc3545;
        /* Cor de fundo do botão ao passar o mouse */
    }


    /* Animação de entrada (slide-in) */
    @keyframes slide-in {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Ícone de sucesso (opcional - requer ícone SVG ou imagem) */
    .modal-content-operacional .success-icon {
        width: 50px;
        height: 50px;
        margin-bottom: 20px;
        fill: #4CAF50;
        /* Cor do ícone SVG */
    }
</style>

</html>