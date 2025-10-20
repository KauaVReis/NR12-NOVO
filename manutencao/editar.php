<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);

// Adiciona uma barra no final se não houver
$base_dir = rtrim($base_dir, '/') . '/';

// Corrige a URL para sempre começar do diretório raiz do projeto
define('BASE_URL', '../../nr12/');
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/nr12/sidebar.php'; ?>

<?php
include '../conexao.php';

require_once '../verifica_permissao.php';
verificaPermissao(['Adm']);

// Obtém o ID da manutenção a ser editada
$id = $_GET['id'];

// Busca os dados da manutenção
$query = "SELECT * FROM manutencao WHERE idmanutencao = :id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$manutencao = $stmt->fetch(PDO::FETCH_ASSOC);

// Carrega as opções de máquinas e colaboradores
$query_maquinas = "SELECT idmaquina, maquina_ni FROM maquina";
$query_colaboradores = "SELECT idcolaborador, colaborador_nome FROM colaborador";

$maquinas = $pdo->query($query_maquinas)->fetchAll(PDO::FETCH_ASSOC);
$colaboradores = $pdo->query($query_colaboradores)->fetchAll(PDO::FETCH_ASSOC);
?>

<form action="atualizar.php" method="POST">
    <input type="hidden" name="idmanutencao" value="<?= $manutencao['idmanutencao'] ?>">

    <label for="maquina_id">ID da Máquina:</label>
    <select name="maquina_id" id="maquina_id">
        <?php foreach ($maquinas as $maquina): ?>
            <option value="<?= $maquina['idmaquina'] ?>" <?= $maquina['idmaquina'] == $manutencao['maquina_id'] ? 'selected' : '' ?>>
                <?= $maquina['maquina_ni'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="colaborador_id">ID do Colaborador:</label>
    <select name="colaborador_id" id="colaborador_id">
        <?php foreach ($colaboradores as $colaborador): ?>
            <option value="<?= $colaborador['idcolaborador'] ?>" <?= $colaborador['idcolaborador'] == $manutencao['colaborador_id'] ? 'selected' : '' ?>>
                <?= $colaborador['colaborador_nome'] ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="box-check_manu">
        <input class="checkbox_manu" type="checkbox" id="tipo_manutencao" name="tipo_manutencao" value="Preventiva" <?= $manutencao['tipo_manutencao'] === 'Preventiva' ? 'checked' : '' ?>>
        <label>Manutenção Preventiva</label>
    </div>
    <div id="campo_data" style="display: <?= $manutencao['tipo_manutencao'] === 'Preventiva' ? 'block' : 'none' ?>">
        <label for="manutencao_data">Data da Manutenção:</label>
        <input type="date" id="manutencao_data" name="manutencao_data" value="<?= $manutencao['manutencao_data'] ?>">
    </div>

    <div id="campo_descricao" style="display: <?= $manutencao['tipo_manutencao'] === 'Corretiva' ? 'block' : 'none' ?>">
        <label for="manutencao_descricao">Descrição do Problema:</label>
        <textarea class="text_manu" id="manutencao_descricao" name="manutencao_descricao" oninput="ajustarAltura(this)" rows="2"><?= $manutencao['manutencao_descricao'] ?></textarea>
    </div>

    <label for="manutencao_estado">Estado da Manutenção:</label>
    <select name="manutencao_estado" id="manutencao_estado">
        <option value="Quebrado" <?= $manutencao['manutencao_estado'] === 'Quebrado' ? 'selected' : '' ?>>Quebrado</option>
        <option value="Consertado" <?= $manutencao['manutencao_estado'] === 'Consertado' ? 'selected' : '' ?>>Consertado</option>
    </select>

    <label for="manutencao_status">Status da Manutenção:</label>
    <select name="manutencao_status" id="manutencao_status">
        <option value="Ativo" <?= $manutencao['manutencao_status'] === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
        <option value="Inativo" <?= $manutencao['manutencao_status'] === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
    </select>

    <label for="manutencao_realizada">Data de Realização:</label>
    <input class="date_manu" type="datetime-local" id="manutencao_realizada" name="manutencao_realizada" value="<?= $manutencao['manutencao_realizada'] !== '0000-00-00 00:00:00' ? $manutencao['manutencao_realizada'] : '' ?>">

    <button class="form-button_manu" type="submit">Salvar Alterações</button>
</form>

<!-- Botão Voltar -->
<button class="voltar" onclick="window.location.href='tabela.php'">Voltar Para Consulta</button>

<script>
    document.getElementById('tipo_manutencao').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('campo_data').style.display = 'block';
            document.getElementById('campo_descricao').style.display = 'none';
            document.getElementById('manutencao_descricao').value = '';
        } else {
            document.getElementById('campo_data').style.display = 'none';
            document.getElementById('campo_descricao').style.display = 'block';
            document.getElementById('manutencao_data').value = '';
        }
    });

    const tipoCheckbox = document.getElementById('tipo_manutencao');
    const dataManutencao = document.getElementById('manutencao_data');

    tipoCheckbox.addEventListener('change', function() {
        if (tipoCheckbox.checked) {
            dataManutencao.disabled = false;
        } else {
            dataManutencao.disabled = true;
            dataManutencao.value = ''; // Limpa o campo se desmarcado
        }
    });

    function ajustarAltura(elemento) {
        elemento.style.height = "auto"; // Reseta a altura
        elemento.style.height = elemento.scrollHeight + "px"; // Ajusta para o conteúdo
    }
</script>

<style>
    .voltar {
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

    .voltar:hover {
        background-color: #fd2020;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        transform: translateY(-3px);
    }
</style>
