<?php

// Habilitar a exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluindo a conexão com o banco de dados
include '../conexao.php';
require_once '../verifica_permissao.php';
verificaPermissao(['Adm', 'Coordenador','Professor']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtemos os dados do formulário
    $tipomaquina = $_POST['tipomaquina'];
    $requisitosSelecionados = $_POST['requisitos'] ?? []; // Se não houver requisitos, será um array vazio

    try {
        // Iniciando uma transação
        $pdo->beginTransaction();

        // Loop pelos requisitos selecionados
        foreach ($requisitosSelecionados as $idRequisito) {
            // Inserindo os requisitos na tabela requisistos
            $sql_insert = "INSERT INTO requisistos (idtipomaquina, idtiporequisito) VALUES (:idtipomaquina, :idtiporequisito)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->bindParam(':idtipomaquina', $tipomaquina);
            $stmt_insert->bindParam(':idtiporequisito', $idRequisito);
            $stmt_insert->execute();
        }

        // Comita a transação
        $pdo->commit();
        echo "Requisitos salvos com sucesso!";
    } catch (PDOException $e) {
        // Se ocorrer um erro, faz o rollback
        $pdo->rollBack();
        echo "Erro ao salvar os requisitos: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Tipo de Máquina</title>
</head>

<body>
    <h1>Cadastro de Tipo de Máquina</h1>
    <form action="cadastro.php" method="post">
        <label>Nome:</label>
        <input type="text" name="nome_tipodemaquina" required>
        <label>Peso:</label>
        <input type="number" name="peso_tipodemaquina" step="0.01" required>
        <label>Fabricante:</label>
        <input type="text" name="fabricante_tipodemaquina" required>
        <label>Modelo:</label>
        <input type="text" name="modelo_tipodemaquina" required>
        <label>Ano de Fabricação:</label>
        <input type="number" name="ano_tipodemaquina" maxlength="4" required>
        <label>Capacidade:</label>
        <input type="text" name="capacidade_tipodemaquina">
        <button type="submit">Enviar</button>
    </form>
</body>

</html>