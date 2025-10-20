<?php
session_start();
include '../conexao.php';

$matricula = $_SESSION['matricula'];
$nimaquina = $_SESSION['nimaquina'];

$maquina_id = null;
$requisitos = [];
$colaboradores = [];
$aluno_id = null;
$requisitos_especificos = [];
$arquivo = null;


try {
    // Busca o idmaquina e tipomaquina_id correspondentes ao maquina_ni na sessão
    $sqlMaquina = "SELECT ma.idmaquina, ma.tipomaquina_id, ma.maquina_ni, ti.tipomaquina_nome 
                   FROM maquina ma 
                   JOIN tipomaquina ti ON ma.tipomaquina_id = ti.idtipomaquina 
                   WHERE ma.maquina_ni = :maquina_ni";

    $stmtMaquina = $pdo->prepare($sqlMaquina);
    $stmtMaquina->bindParam(':maquina_ni', $nimaquina);
    $stmtMaquina->execute();
    $maquina = $stmtMaquina->fetch(PDO::FETCH_ASSOC);

    if ($maquina) {
        $maquina_id = $maquina['idmaquina'];
        $tipomaquina_id = $maquina['tipomaquina_id'];

        // Consulta para buscar o arquivo associado ao tipomaquina
        $sqlArquivo = "SELECT tipomaquina_arquivo FROM tipomaquina WHERE idtipomaquina = :tipomaquina_id";
        $stmtArquivo = $pdo->prepare($sqlArquivo);
        $stmtArquivo->bindParam(':tipomaquina_id', $tipomaquina_id);
        $stmtArquivo->execute();
        $resultArquivo = $stmtArquivo->fetch(PDO::FETCH_ASSOC);
        if ($resultArquivo) {
            $arquivo = $resultArquivo['tipomaquina_arquivo'];
        }

        // Consulta para os requisitos de segurança associados ao tipo da máquina
        $sqlRequisitos = "
            SELECT r.idrequisitos, r.requisito_topico
            FROM tipomaquina_requisito tr
            JOIN requisitos r ON tr.requisitos_id = r.idrequisitos
            WHERE tr.tipomaquina_id = :tipomaquina_id AND r.tipo_req = 'Seguranca'
        ";
        $stmtRequisitos = $pdo->prepare($sqlRequisitos);
        $stmtRequisitos->bindParam(':tipomaquina_id', $tipomaquina_id);
        $stmtRequisitos->execute();
        $requisitos = $stmtRequisitos->fetchAll(PDO::FETCH_ASSOC);
    } else {
        echo "Nenhuma máquina encontrada com esse NI.";
    }

    // Busca o ID do aluno com base na matrícula
    $sqlAluno = "SELECT idaluno, turmas_id, aluno_nome 
                 FROM aluno 
                 WHERE aluno_matricula = :matricula";
    $stmtAluno = $pdo->prepare($sqlAluno);
    $stmtAluno->bindParam(':matricula', $matricula);
    $stmtAluno->execute();
    $aluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);

    if ($aluno) {
        $aluno_id = $aluno['idaluno'];
        $turmas = $aluno['turmas_id'];
    } else {
        echo "Matrícula não encontrada.";
    }
} catch (PDOException $e) {
    echo "Erro ao buscar dados: " . $e->getMessage();
}

$sqlTurma = "SELECT turma_nome, curso_id FROM turmas WHERE idturmas = :turmas_id";
$stmtTurma = $pdo->prepare($sqlTurma);
$stmtTurma->bindParam(':turmas_id', $turmas);
$stmtTurma->execute();
$turma = $stmtTurma->fetch(PDO::FETCH_ASSOC);

$curso_nome = "";

if ($turma) {
    $curso_id = $turma['curso_id'];
    $turma_nome = $turma['turma_nome'];
    $sqlCurso = "SELECT curso_nome FROM curso WHERE idcurso = :curso_id";
    $stmtCurso = $pdo->prepare($sqlCurso);
    $stmtCurso->bindParam(':curso_id', $curso_id);
    $stmtCurso->execute();
    $curso = $stmtCurso->fetch(PDO::FETCH_ASSOC);

    if ($curso) {
        $curso_nome = $curso['curso_nome'];
    }
} else {
    echo "Turma não encontrada.";
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilos.css">
    <title>Verificação de Máquina</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #bbb;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 0 20px;
        }

        .box {
            background-color: white;
            width: 100%;
            max-width: 450px;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .logo {
            width: 100%;
            max-width: 250px;
            display: block;
            margin: 0 auto 20px;
        }

        .info-box {
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            text-align: center;
            height: auto;
            /* Adapta a altura ao conteúdo */
        }

        .btn-check {
            width: 100%;
            padding: 15px;
            background-color: #ff3535;
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }

        .btn-check:hover {
            background-color: #bbb;
            color: #ff3535;
            font-weight: bolder;
        }

        .pdf {
            align-self: center;
            text-decoration: none;
            color: #000;
        }

        i {
            color: red;
            font-size: larger;
            margin: 6px;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .box {
                padding: 20px;
            }

            .btn-check {
                font-size: 14px;
                padding: 12px;
            }

            .logo {
                max-width: 200px;
            }

            i {
                font-size: medium;
                margin: 4px;
            }
        }

        @media (max-width: 480px) {
            .box {
                padding: 15px;
            }

            .btn-check {
                font-size: 12px;
                padding: 10px;
            }

            .logo {
                max-width: 150px;
            }

            i {
                font-size: smaller;
                margin: 3px;
            }
        }

        .container-btn-voltar{
            margin-top: 10px;
        }
        </style>
</head>
<body>
    <div class="box">
        <img src="../imagem/senailogo.png" alt="SENAI Logo" class="logo">
        
        <!-- Informações exibidas diretamente -->
        <div class="info-box">
            <p>Seja bem-vindo(a):</p> <?= htmlspecialchars($aluno['aluno_nome']) ?>
            <p>Turmas: </p><?= htmlspecialchars($turma_nome) ?> - <?= htmlspecialchars($curso_nome) ?>
        </div>
        
        <div class="info-box">
            <p>NI da máquina escolhida: <?= htmlspecialchars($maquina['maquina_ni']) ?></p>
            <p>Tipo de máquina: <?= htmlspecialchars($maquina['tipomaquina_nome']) ?></p>
        </div>
        
        <!-- Link para o arquivo -->
        <div class="info-box">
            <?php if ($arquivo): ?>
                <a href="<?php echo htmlspecialchars($arquivo); ?>" target="_blank" class="pdf">
                    <i class="fa-solid fa-file-pdf"></i> visualizar pdf de apoio
                </a>
            <?php else: ?>
                <p>Nenhum arquivo disponível para este tipo de máquina.</p>
            <?php endif; ?>
        </div>

        <button onclick="window.location.href='seguranca.php'" class="btn-check">CHECKLIST DE SEGURANÇA</button>
        <button onclick="window.location.href='operacional.php'" class="btn-check">CHECKLIST OPERACIONAL</button>

        <button class="btn-check" onclick="window.location.href='../documentacao/ALUNO.docx.pdf'">MANUAL DO ALUNO</button>
    </div>

    <div class="container-btn-voltar">
        <button class="boton-elegante" onclick="voltar()">Voltar</button>
    </div>

    <script>
        function voltar() {
            window.location.href = '../logout.php';
        }
    </script>
</body>
</html>