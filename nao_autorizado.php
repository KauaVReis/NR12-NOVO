<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/estilos.css">
    <title>Acesso Não Autorizado</title>
</head>

<body class="acesso_negado">
    <div class="nao-autorizado">
        <h1>Acesso Negado</h1>
        <p>Desculpe, você não tem permissão para acessar esta página.</p>
        <button onclick="history.back()">Voltar</button>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = "<?php echo BASE_URL; ?>home.php";
        }, 5000); // Redireciona após 5 segundos (5000 milissegundos).
    </script>

</body>

</html>