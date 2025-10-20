<?php
include '../conexao.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Verifica o status atual
    $stmt = $pdo->prepare("SELECT curso_status FROM curso WHERE idcurso = :id");
    $stmt->execute(['id' => $id]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($curso) {
        $novoStatus = ($curso['curso_status'] === 'Ativo') ? 'Inativo' : 'Ativo';
        // Atualiza o status
        $updateStmt = $pdo->prepare("UPDATE curso SET curso_status = :status WHERE idcurso = :id");
        $updateStmt->execute(['status' => $novoStatus, 'id' => $id]);
    }
}
header("Location: {$_SERVER['HTTP_REFERER']}");
exit;
