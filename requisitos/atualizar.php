<?php
session_start();
include '../conexao.php'; // Conexão com o banco de dados
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador','Professor']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $tipo_req = $_POST['tipo_req'];
    $requisito_topico = $_POST['requisito_topico'];

    try {
        $sql = "UPDATE requisitos SET tipo_req = :tipo_req, requisito_topico = :requisito_topico WHERE idrequisitos = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tipo_req', $tipo_req);
        $stmt->bindParam(':requisito_topico', $requisito_topico);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Mensagem de sucesso
        $_SESSION['mensagem'] = "Requisito atualizado com sucesso!";
        $_SESSION['tipo_mensagem'] = "success"; // Ou "error" dependendo do resultado

        // Redireciona para a lista de requisitos
        header('Location: consultar.php'); // Mude para o nome correto do seu arquivo de lista
        exit();
    } catch (PDOException $e) {
        die("Erro ao atualizar requisito: " . $e->getMessage());
    }
}
?>
