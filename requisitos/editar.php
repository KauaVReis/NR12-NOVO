<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);

// Adiciona uma barra no final se não houver
$base_dir = rtrim($base_dir, '/') . '/';

// Corrige a URL para sempre começar do diretório raiz do projeto
// define('BASE_URL', '../../nr12/');
?>

<?php include __DIR__ . '/../sidebar.php'; ?>

<?php
// Incluindo a conexão com o banco de dados
include '../conexao.php';

require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador']);

try {
    // Definindo o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificando se o ID foi passado pela URL
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']); // Certifique-se de que o ID é um número inteiro

        // Consulta SQL para buscar o requisito pelo ID
        $sql = "SELECT 
                    r.idrequisitos,
                    r.tipo_req,
                    r.requisito_topico,
                    r.requisitos_status
                FROM requisitos r
                WHERE r.idrequisitos = :id";

        $stmt = $pdo->prepare($sql);
        // Vincule o parâmetro :id
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Use o nome correto da variável aqui
            $requisito = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            die("Requisito não encontrado.");
        }

    } else {
        die("ID não fornecido.");
    }

    // Consulta para buscar todos os tipos de requisição
    $sqlTiposReq = "SELECT DISTINCT tipo_req FROM requisitos"; // Ou a tabela que contém os tipos de requisições
    $stmtTiposReq = $pdo->query($sqlTiposReq);
    $tiposReq = $stmtTiposReq->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para buscar todos os tópicos
    $sqlTópicos = "SELECT requisito_topico FROM requisitos"; // Ou a tabela que contém os tópicos
    $stmtTópicos = $pdo->query($sqlTópicos);
    $tópicos = $stmtTópicos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao buscar requisito: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Requisito</title>
    <link rel="stylesheet" href="../css/estilos.css">

    <style>
        body{
            display: flex;
            justify-content: center;
            text-align: center;
        }
       
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }

        /* Estilos para a mensagem de sucesso/erro */
        .toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px;
            border-radius: 5px;
            font-size: 16px;
            display: none;
            z-index: 1000;
        }
        .toast.sucesso {
            background-color: #28a745;
            color: white;
        }
        .toast.erro {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

<h1>Editar Requisito</h1>

<form class='formEditarRequisitos' action="atualizar.php" method="POST">
    <input type="hidden" name="id" value="<?= htmlspecialchars($requisito['idrequisitos']) ?>">

    <label for="tipo_req">Tipo de Requisição:</label>
    <select name="tipo_req" id="tipo_req" required>
        <?php foreach ($tiposReq as $tipo): ?>
            <option value="<?= htmlspecialchars($tipo['tipo_req']) ?>" <?= ($tipo['tipo_req'] == $requisito['tipo_req']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($tipo['tipo_req']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="requisito_topico">Tópico:</label>
    <input type="text" id="requisito_topico" name="requisito_topico" value="<?= htmlspecialchars($requisito['requisito_topico']) ?>" required><br>

    <input type="submit" value='Atualizar Requisito'>
    
</form>
<a href="consultar.php" class="lista_requisitos">Ir para a lista de Requisitos</a>


<div id="toast" class="toast"></div>

<script>
    <?php if (isset($_SESSION['mensagem'])): ?>
        let mensagem = "<?= addslashes($_SESSION['mensagem']); ?>";
        let tipoMensagem = "<?= $_SESSION['tipo_mensagem']; ?>";
        let toast = document.getElementById('toast');

        function showToast(message, type) {
            toast.innerHTML = message;
            toast.classList.add('show', type);
            toast.style.display = 'block'; // Certifique-se de que o toast está visível
            setTimeout(() => {
                toast.classList.remove('show', type);
                toast.style.display = 'none'; // Esconde o toast após a exibição
                // Redireciona após o tempo do toast
                window.location.href = "http://localhost/nr12/requisitos/consultar.php";
            }, 3000);
        }

        showToast(mensagem, tipoMensagem);

        <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
    <?php endif; ?>
</script>

</body>
</html>
