<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);

// Adiciona uma barra no final se não houver
$base_dir = rtrim($base_dir, '/') . '/';

// Corrige a URL para sempre começar do diretório raiz do projeto
define('BASE_URL', '../../nr12/');
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/nr12/sidebar.php'; ?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
    <title>Documentação NR12</title>
</head>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #cfcfcf;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    header {
        background-color: #2c3e50;
        color: white;
        text-align: center;
        padding: 20px 0;
    }

    h1 {
        margin: 0;
    }

    .principal {
        margin-top: 170px;
        padding: 0 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .cabecalho h1 {
        font-size: 2.5rem;
    }

    .conteudo {
        max-width: 900px;
        margin: 0 auto;
        background-color: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .conteudo h2 {
        font-size: 1.8rem;
        color: #333;
        margin-bottom: 10px;
    }

    .sobrenr12 {
        font-size: 1.1rem;
        line-height: 1.6;
        color: #555;
    }

    .lista {
        margin-top: 10px;
        font-size: 1.1rem;
        line-height: 1.6;
        color: #555;
        list-style: none;
        /* Garantir que o ponto de lista fique fora do texto */
    }

    .lista li {
        margin-bottom: 10px;
        padding-left: 25px;
        /* Ajuste o espaçamento do texto em relação ao ponto */
        list-style-type: none;
        /* Definindo o tipo de marcador como disco */
    }

    .secao-documentacao {
        background-color: #fff;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    .link-documentacao {
        font-size: 1.2rem;
        color: #333;
        margin-bottom: 10px;
    }

    .botao-documentacao {
        display: inline-block;
        font-size: 1rem;
        padding: 12px 20px;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        background-color: #e21616;
    }

    .botao-documentacao:hover {
        background-color: #a50008;
    }
    

    footer {
        text-align: center;
        padding: 10px 0;
        background-color: #2c3e50;
        color: white;
        margin-top: 50px;
        width: 100%;
    }
</style>

<body class="corpo">
    <main class="principal">
        <header class="cabecalho">
            <h1>Documentação da NR12</h1>
        </header>

        <section class="conteudo">
            <h2>O que é a NR12?</h2>
            <p class="sobrenr12">A NR12 é uma norma regulamentadora que estabelece referências técnicas para garantir a
                segurança no trabalho em máquinas e equipamentos. Seu objetivo é prevenir acidentes e doenças
                ocupacionais, assegurando um ambiente de trabalho seguro.</p>

            <h2>Objetivos da NR12</h2>
            <ul class="lista">
                <li>Proteger a saúde e a integridade física dos trabalhadores;</li>
                <li>Estabelecer requisitos para a utilização segura de máquinas e equipamentos;</li>
                <li>Promover a melhoria das condições de trabalho.</li>
            </ul>
        </section>

        <aside class="secao-documentacao">
            <p class="link-documentacao">Dúvidas? Consulte nossa documentação:</p>
            <?php
            $colaborador_permissao = $_SESSION['colaborador_permissao'];

            if($colaborador_permissao=='Adm' || $colaborador_permissao=='Coordenador'){
                echo'
                <a href="./Coordenador.docx" class="botao-documentacao">
                    <i class="fas fa-download"></i> Baixar Documentação
                </a>
                ';
            }
            else{
                echo'
                <a href="./Professor01.docx" class="botao-documentacao">
                    <i class="fas fa-download"></i> Baixar Documentação
                </a>
                ';
            }
            ?>
           
        </aside>
    </main>

</body>

</html>