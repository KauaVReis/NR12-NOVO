<?php
session_start();
include '../conexao.php';

require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $tipo_funcionario = $_POST['tipo_funcionario'];
    $nif = isset($_POST['nif']) ? $_POST['nif'] : null;
    $opcoes = [
        'memory_cost' => 1<<17,    // 128 MB de memória (configuração moderada)
        'time_cost'   => 4,        // Tempo de processamento
        'threads'     => 2         // Paralelismo
    ];
    $senha = password_hash('senaisp', PASSWORD_ARGON2ID, $opcoes);    
    $setor_id = $_POST['setor'];

    try {
        // Verifica se o email ou NIF já existem no banco de dados
        $sqlVerifica = "SELECT * FROM colaborador WHERE colaborador_email = :email OR colaborador_nif = :nif";
        $stmtVerifica = $pdo->prepare($sqlVerifica);
        $stmtVerifica->bindParam(':email', $email);
        $stmtVerifica->bindParam(':nif', $nif);
        $stmtVerifica->execute();

        if ($stmtVerifica->rowCount() > 0) {
            // Email ou NIF já cadastrado
            $_SESSION['mensagem'] = "Erro: Email ou NIF já cadastrado!";
            $_SESSION['mensagem_tipo'] = "erro";
        } else {
            // Se não houver duplicidade, insere no banco
            $sqlInserir = "INSERT INTO colaborador (colaborador_nome, colaborador_email, colaborador_nif, senha, setor_id, colaborador_permissao) VALUES (:nome, :email, :nif, :senha, :setor_id, :permissao)";
            $stmtInserir = $pdo->prepare($sqlInserir);
            $stmtInserir->bindParam(':nome', $nome);
            $stmtInserir->bindParam(':email', $email);
            $stmtInserir->bindParam(':nif', $nif);
            $stmtInserir->bindParam(':senha', $senha);
            $stmtInserir->bindParam(':setor_id', $setor_id, PDO::PARAM_INT);
            $stmtInserir->bindParam(':permissao', $tipo_funcionario);

            if ($stmtInserir->execute()) {
                $_SESSION['mensagem'] = "Cadastro realizado com sucesso!";
                $_SESSION['mensagem_tipo'] = "sucesso";
            } else {
                $_SESSION['mensagem'] = "Erro: Falha ao cadastrar o funcionário.";
                $_SESSION['mensagem_tipo'] = "erro";
            }
        }
        header("Location: ./cadastro.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro: " . htmlspecialchars($e->getMessage());
        $_SESSION['mensagem_tipo'] = "erro";
        header("Location: ./cadastro.php");
        exit();
    }
}
$pdo = null;
?>
