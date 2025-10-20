<?php
include '../conexao.php'; // Verifique se o caminho está correto

$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');


include __DIR__ . '/../sidebar.php';

require_once '../verifica_permissao.php';
verificaPermissao(permissoesPermitidas: ['Adm']);

// Verifica se o ID do colaborador foi passado
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta para obter os dados do colaborador
    $sql = "SELECT c.idcolaborador, c.colaborador_nome, c.colaborador_email, c.senha, c.colaborador_nif, c.setor_id, s.setor_nome 
            FROM colaborador c 
            LEFT JOIN setor s ON c.setor_id = s.idsetor 
            WHERE c.idcolaborador = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $colaborador = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            echo "Colaborador não encontrado.";
            exit;
        }
    } catch (PDOException $e) {
        echo "Erro: " . htmlspecialchars($e->getMessage());
        exit;
    }
} else {
    echo "ID não fornecido.";
    exit;
}

// Consulta para obter todos os setores
$sqlSetores = "SELECT idsetor, setor_nome FROM setor WHERE setor_status = 'Ativo'";
$setores = $pdo->query($sqlSetores)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>../css/estilos.css">
</head>

<body style="display: flex; text-align: center; justify-content: center;">
    <h2>Editar Funcionário</h2>
        <!-- Exibe a mensagem de erro ou sucesso, caso exista -->
        <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="modal <?php echo $_SESSION['mensagem_tipo']; ?>">
            <?php echo htmlspecialchars($_SESSION['mensagem']); ?>
        </div>
        <?php unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']); ?> <!-- Limpa a mensagem da sessão após exibir -->
    <?php endif; ?>
    <form action="atualizar.php" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($colaborador['idcolaborador']); ?>">

        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome"
            value="<?php echo htmlspecialchars($colaborador['colaborador_nome']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email"
            value="<?php echo htmlspecialchars($colaborador['colaborador_email']); ?>" required>

        <label for="senha" style="display:none;">Senha:</label>
        <input type="password" id="senha" name="senha" hidden placeholder="Digite uma nova senha apenas se desejar alterá-la">

        <label for="nif">NIF:</label>
        <input type="text" id="nif" name="nif" inputmode="numeric" pattern="[0-9]*" maxlength="7"
            value="<?php echo htmlspecialchars($colaborador['colaborador_nif']); ?>" required
            oninput="this.value = this.value.replace(/[^0-9]/g, '');">

        <label for="setor">Setor:</label>
        <select id="setor" name="setor" required>
            <option value="">Selecione um setor</option>
            <?php foreach ($setores as $setor): ?>
                <option value="<?php echo htmlspecialchars($setor['idsetor']); ?>" <?php echo ($setor['idsetor'] == $colaborador['setor_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($setor['setor_nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <style>
            @media (max-width: 768px) {
                h2 {
                    font-size: 30px;
                }

                body {
                    margin-top: 60px;
                }

                form {
                    width: 65%;
                    padding: 15px;
                }
            }

            @media (max-width: 480px) {
                h2 {
                    font-size: 30px;
                }

                body {
                    margin-top: 60px;
                }

                form {
                    width: 85%;
                    padding: 10px;
                }
            }
            .modal-erro {
                display: block;
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: #f44336;
                color: white;
                padding: 20px;
                border-radius: 8px;
                text-align: center;
                z-index: 1000;
            }

            .modal-erro .close-btn {
                display: block;
                margin-top: 10px;
                color: #fff;
                text-decoration: underline;
                cursor: pointer;
            }
        </style>

        <input type="submit" value="Atualizar">
    </form>

    <a href="consulta.php" class="lista_funcionarios">Ir para a lista de funcionários</a>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const modalErro = document.querySelector('.modal-erro');
            if (modalErro) {
                setTimeout(() => {
                    modalErro.style.display = 'none';
                }, 5000); // Fecha após 5 segundos
            }
        });
    </script>

</body>

</html>
