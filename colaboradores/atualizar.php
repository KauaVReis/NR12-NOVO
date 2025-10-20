<?php
session_start();
include '../conexao.php'; // Inclui a conexão com o banco de dados

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe os dados do formulário e sanitiza para evitar SQL Injection e XSS
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = !empty($_POST['senha']) ? $_POST['senha'] : null; // A senha pode ser opcional
    $nif = filter_input(INPUT_POST, 'nif', FILTER_SANITIZE_STRING);
    $setor = filter_input(INPUT_POST, 'setor', FILTER_VALIDATE_INT);

    if (!$id || !$nome || !$email || !$nif || !$setor) {
        $_SESSION['mensagem'] = 'Erro: Todos os campos obrigatórios devem ser preenchidos corretamente!';
        $_SESSION['mensagem_tipo'] = 'erro';
        header('Location: alterar.php?id=' . $id); 
        exit;
    }

    // Verifica se o NIF já existe no banco de dados para outro colaborador (exceto o colaborador atual)
    $sqlCheckNif = "SELECT COUNT(*) FROM colaborador WHERE colaborador_nif = :nif AND idcolaborador != :id";
    $stmtCheckNif = $pdo->prepare($sqlCheckNif);
    $stmtCheckNif->bindParam(':nif', $nif, PDO::PARAM_STR);
    $stmtCheckNif->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtCheckNif->execute();

    if ($stmtCheckNif->fetchColumn() > 0) {
        // Se o NIF já existe para outro colaborador, define a mensagem de erro e armazena na sessão
        $_SESSION['mensagem'] = 'Erro: NIF já cadastrado!';
        $_SESSION['mensagem_tipo'] = 'erro'; // Define o tipo da mensagem (erro)
        
        // Faz o redirecionamento para o formulário com a mensagem
        header('Location: alterar.php?id=' . $id); 
        exit;
    }

    // Caso o NIF não seja duplicado, procede com a atualização
    try {
        // Atualiza os dados do colaborador
        $sqlUpdate = "UPDATE colaborador SET 
            colaborador_nome = :nome, 
            colaborador_email = :email, 
            colaborador_nif = :nif, 
            setor_id = :setor 
            WHERE idcolaborador = :id";

        // Se uma nova senha foi fornecida, também a atualiza
        if (!empty($senha)) {
            $sqlUpdate = "UPDATE colaborador SET 
                colaborador_nome = :nome, 
                colaborador_email = :email, 
                colaborador_nif = :nif, 
                senha = :senha, 
                setor_id = :setor 
                WHERE idcolaborador = :id";
        }

        $stmt = $pdo->prepare($sqlUpdate);
        $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':nif', $nif, PDO::PARAM_STR);
        $stmt->bindParam(':setor', $setor, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if (!empty($senha)) {
            $opcoes = [
                'memory_cost' => 1<<17,    // 128 MB de memória (configuração moderada)
                'time_cost'   => 4,        // Tempo de processamento
                'threads'     => 2         // Paralelismo
            ];
            $stmt->bindParam(':senha', password_hash($senha, PASSWORD_ARGON2ID, $opcoes), PDO::PARAM_STR); // Hash da senha
        }

        $stmt->execute();

        // Sucesso
        $_SESSION['mensagem'] = 'Colaborador atualizado com sucesso!';
        $_SESSION['mensagem_tipo'] = 'sucesso';
        header('Location: consulta.php'); // Redireciona para a página de consulta
        exit;
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = 'Erro ao atualizar o colaborador: ' . $e->getMessage();
        $_SESSION['mensagem_tipo'] = 'erro';
        header('Location: editar.php?id=' . $id); // Redireciona para o formulário em caso de erro
        exit;
    }
}
?>

<!-- Código para exibir o modal -->
<?php if (isset($_SESSION['mensagem'])): ?>
    <div class="modal <?php echo $_SESSION['mensagem_tipo']; ?>" id="modalMensagem">
        <p><?php echo htmlspecialchars($_SESSION['mensagem']); ?></p>
        <button onclick="fecharModal()">Fechar</button>
    </div>
    <?php unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']); ?>
<?php endif; ?>

<script>
    // Função para fechar o modal
    function fecharModal() {
        document.getElementById('modalMensagem').style.display = 'none';
    }

    // Exibe o modal automaticamente se houver uma mensagem
    window.onload = function() {
        const modal = document.getElementById('modalMensagem');
        if (modal) {
            modal.style.display = 'block';
        }
    };
</script>

<style>
    /* Estilo básico para o modal */
    .modal {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        text-align: center;
        z-index: 1000;
    }
    .modal.erro {
        border: 2px solid red;
    }
    .modal.sucesso {
        border: 2px solid green;
    }
    .modal button {
        margin-top: 10px;
        padding: 5px 10px;
    }
</style>
