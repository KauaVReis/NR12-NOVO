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
    $tipo_req = $_POST['tipos_requisitos'];
    try {
        // Iniciando uma transação
        $pdo->beginTransaction();

        // Loop pelos requisitos selecionados
        foreach ($requisitosSelecionados as $idRequisito) {
            // Inserindo os requisitos na tabela requisitos
            $sql_insert = "INSERT INTO requisitos (tipomaquina_id, tiporequisito_id, tipo_req) VALUES (:tipomaquina_id, :tiporequisito_id, :tipo_req)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->bindParam(':tipomaquina_id', $tipomaquina);
            $stmt_insert->bindParam(':tiporequisito_id', $idRequisito);
            $stmt_insert->bindParam(':tipo_req', $tipo_req);
            $stmt_insert->execute();
        }

        // Comita a transação
        $pdo->commit();
        echo "Requisitos salvos com sucesso!";
        
        // header("Location: ./consultar.php");
    } catch (PDOException $e) {
        // Se ocorrer um erro, faz o rollback
        $pdo->rollBack();
        echo "Erro ao salvar os requisitos: " . $e->getMessage();
    }
}
?>
