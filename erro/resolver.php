<?php
session_start();
include('../conexao.php');

require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador']);

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo "<div style='color: red; text-align: center;'>Usuário não está logado.</div>";
    exit; // Para evitar continuar a execução do código
}

// Verifica se o ID da solicitação foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['idsolicitacao_erro'])) {
    $idsolicitacao_erro = $_POST['idsolicitacao_erro'];

    // Captura a data atual
    $data_solucao = date('Y-m-d H:i:s'); // Formato: YYYY-MM-DD HH:MM:SS

    // Atualiza a situação da solicitação e a data de solução
    $sql = "UPDATE solicitacao_erro SET situacao = 'Resolvido', data_solucao = :data_solucao WHERE idsolicitacao_erro = :idsolicitacao_erro";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idsolicitacao_erro', $idsolicitacao_erro);
        $stmt->bindParam(':data_solucao', $data_solucao);

        if ($stmt->execute()) {
            echo "Solicitação marcada como resolvida com sucesso!";
            header('location: consultar.php');
        } else {
            echo "Erro ao atualizar a solicitação: " . implode(", ", $stmt->errorInfo());
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
