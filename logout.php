<?php
// logout.php

// Inicia a sessão
session_start();

// Destrói todas as variáveis da sessão
session_unset();

// Destrói a sessão
session_destroy();

// Redireciona o usuário para a página de login ou home
header("Location: Login/login.php"); // Substitua "login.php" pelo caminho para sua página de login ou inicial
exit;
    