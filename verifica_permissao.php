<?php

function verificaPermissao($permissoesPermitidas = [])
{
    // Verifica se a sessão de permissão existe e se é válida
    if (
        !isset($_SESSION['colaborador_permissao']) ||
        !in_array(strtoupper($_SESSION['colaborador_permissao']), array_map('strtoupper', $permissoesPermitidas))
    ) {
        // Redireciona para uma página de "não autorizado" caso a permissão não seja permitida
        echo "<script>window.location.href = '../nao_autorizado.php'</script>";
        exit();
    }
}
