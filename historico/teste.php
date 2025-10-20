<?php
include '../conexao.php';
session_start();
try {
    $maquina_ni = $_SESSION['nimaquina'];
     $matricula = $_SESSION['matricula'];
     $idmaquina = $_SESSION['idmaquina'];

    // Consulta SQL
    $sql = "
        SELECT 
            m.maquina_ni, 
            tm.tipomaquina_nome, 
            r.idrequisitos, 
            tr.tiporequisito_topico
           
        FROM 
            maquina m
        JOIN 
            tipomaquina tm ON m.tipomaquina_id = tm.idtipomaquina
        JOIN 
            requisitos r ON r.tipomaquina_id = tm.idtipomaquina
        JOIN 
            tiporequisito tr ON r.tiporequisito_id = tr.idtiporequisito
        WHERE 
            m.maquina_ni = :ni
        
    ";

    // Preparar a consulta
    $stmt = $pdo->prepare($sql);
    // Executar a consulta com o NI da máquina
    $stmt->execute([':ni' => $maquina_ni]);
    // Buscar os resultados
    $requisitos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Exibir os resultados
    if ($requisitos) {
        foreach ($requisitos as $requisito) {
           
            echo "<br><input type='checkbox'>Tópico: " . $requisito['tiporequisito_topico'];

        }
    } else {
        echo "Nenhum requisito encontrado para a máquina com NI: " . $maquina_ni;
    }
} catch (PDOException $e) {
    echo "Erro ao executar consulta: " . $e->getMessage();
}
?>
