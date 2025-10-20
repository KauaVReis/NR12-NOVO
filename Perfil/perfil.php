<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);

// Adiciona uma barra no final se não houver
$base_dir = rtrim($base_dir, '/') . '/';

// Corrige a URL para sempre começar do diretório raiz do projeto
// define('BASE_URL', '../../nr12/');
?>
<?php include __DIR__ . '/../sidebar.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil do Usuário</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>

<body class="perfil-body">
  <!-- Conteúdo da Página de Perfil -->
  <div class="perfil-container">
    <div class="perfil-card">
      <div class="perfil-detalhes">
        <p>Email: <?php echo htmlspecialchars($_SESSION['colaborador_email']); ?></p>
        <p>Função: <?php echo htmlspecialchars($_SESSION['colaborador_permissao']); ?></p>
      </div>
    </div>
    <div class="perfil-opcoes">
      <a class="perfil-btn-sair" href="../../nr12/logout.php">Sair</a>
    </div>
  </div>

  <script>

    const isMobile = window.matchMedia("(max-width: 768px)").matches;

    if (isMobile) {
      const flipCards = document.querySelectorAll(".flip-card");

      flipCards.forEach(card => {
        card.addEventListener("click", () => {
          const innerCard = card.querySelector(".flip-card-inner");
          innerCard.classList.toggle("flipped");
        });
      });
    }
  </script>

</body>

<style>
  /*------------------------------/Começo do Perfil\------------------------------*/
  @media (max-width: 550px) {
    .perfil-container {
      width: 100px;

    }
  }

  .perfil-container {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: rgba(50, 50, 93, 0.25) 0px 50px 100px -20px, rgba(0, 0, 0, 0.3) 0px 30px 60px -30px, rgba(10, 37, 64, 0.35) 0px -2px 6px 0px inset;
    padding: 40px;
    max-width: 400px;
    width: 100%;
    text-align: center;
    margin-top: 350px;
    /* Ajuste este valor conforme necessário */
  }

  .perfil-card {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .perfil-foto img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
  }

  .perfil-detalhes h1 {
    color: #333;
    font-size: 24px;
    margin: 10px 0;
  }

  .perfil-detalhes p {
    color: #777;
    font-size: 16px;
    margin: 5px 0;
  }

  .perfil-opcoes {
    margin-top: 20px;
  }

  .perfil-btn-editar,
  .perfil-btn-sair {
    background-color: #e21616;
    color: white;
    border: none;
    padding: 10px 20px;
    margin: 5px;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
    text-decoration: none;
  }

  .perfil-btn-editar:hover,
  .perfil-btn-sair:hover {
    background-color: #e03838;
  }

  /*------------------------------/Fim do Perfil\------------------------------*/
</style>

</html>