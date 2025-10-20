<?php
// Inclui a conexão com o banco de dados
include '../conexao.php'; // Certifique-se de que o caminho está correto
session_start();


require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador', 'Professor']);

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe os dados do formulário
    $id_colaborador = $_POST['id_colaborador']; // Obtém o ID do colaborador
    $desc_erro = $_POST['desc_erro']; // Captura a descrição do erro

    // Captura a data atual
    $data_solicitacao = date('Y-m-d H:i:s'); // Formato: YYYY-MM-DD HH:MM:SS

    // Prepara a consulta
    $sql = "INSERT INTO solicitacao_erro (id_colaborador, desc_erro, data_solicitacao) VALUES (:id_colaborador, :desc_erro, :data_solicitacao)";

    try {
        // Prepara a declaração
        $stmt = $pdo->prepare($sql);

        // Bind dos parâmetros
        $stmt->bindParam(':id_colaborador', $id_colaborador);
        $stmt->bindParam(':desc_erro', $desc_erro);
        $stmt->bindParam(':data_solicitacao', $data_solicitacao);

        // Executa a consulta
        if ($stmt->execute()) {
            echo "Cadastro realizado com sucesso!";
            // Redireciona para outra página ou exibe uma mensagem
            header("Location: ./cadastrar.php");
            exit(); // Certifica-se de que o script é encerrado após o redirecionamento
        } else {
            echo "Erro ao cadastrar: " . implode(", ", $stmt->errorInfo());
        }
    } catch (PDOException $e) {
        echo "Erro: " . htmlspecialchars($e->getMessage());
    }
} else {
    echo "Nenhum dado recebido.";
}

// Fecha a conexão (opcional com PDO)
$pdo = null;
?>
