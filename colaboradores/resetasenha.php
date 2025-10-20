<?php
include '../conexao.php';

session_start();
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador']);

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $usuarioId = $_GET['id'];
    $novaSenha = 'senaisp'; // Nova senha recebida do formulário
    $opcoes = [
        'memory_cost' => 1<<17,    // 128 MB de memória (configuração moderada)
        'time_cost'   => 4,        // Tempo de processamento
        'threads'     => 2         // Paralelismo
    ];
    $novaSenhaHash = password_hash($novaSenha, PASSWORD_ARGON2ID);

    // Atualiza a senha no banco de dados
    $stmt = $pdo->prepare("UPDATE colaborador SET senha = ?, senha_padrao = 1 WHERE idcolaborador = ?");
    $stmt->bindParam(1, $novaSenhaHash, PDO::PARAM_STR);
    $stmt->bindParam(2, $usuarioId, PDO::PARAM_INT);

    // Verifica se a atualização foi bem-sucedida
    if ($stmt->execute()) {
        $_SESSION['mensagem'] = "Senha redefinida com sucesso!";
        $_SESSION['mensagem_tipo'] = "sucesso";
    } else {
        $_SESSION['mensagem'] = "Erro ao redefinir a senha.";
        $_SESSION['mensagem_tipo'] = "erro";
    }

    // Exibe o Modal de resultado
    echo "<script>
                window.location.href = 'consulta.php';
          </script>";
} else {
    echo "Solicitação inválida.";
}
