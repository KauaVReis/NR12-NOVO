<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="//use.fontawesome.com/releases/v5.0.7/css/all.css">
</head>
<?php require 'sidebar.php'; ?>

<style>
  .nr12-image-container {
    margin-top: 10px;
    margin-left: 10px;

  }

  main {
    margin: 10 auto;
    /* Centraliza o main no contêiner pai */
    max-width: 1600px;
    /* Definindo largura máxima para o main */
    overflow: hidden;

  }

  .intro,
  .services {
    text-align: center;
  }

  h2 {
    color: #a50008;
    margin-bottom: 2rem;
    /* Diminua para ajustar a posição */
    font-size: 40px;
    /* Ajuste aqui também */
  }

  h5 {
    color: #333;
    font-size: 20px;
    max-width: 1250px;
    margin: 0 auto;
    line-height: 1.8;
    /* Ajuste aqui também */
  }

  p {
    margin-bottom: 2rem;
    color: #fff;
    max-width: 600px;
    margin: 0 auto;
    line-height: 2.8;
  }

  .services {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    /* Permitir quebra para telas menores */
  }

  .service {
    background-color: #a50008;
    color: white;
    border-radius: 10px;
    width: 200px;
    height: 150px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }

  footer {
    background-color: #333;
    color: #fff;
    text-align: center;
    padding: 1rem;
  }

  .documentation-link {
    margin-top: 2rem;
    color: #8B0000;
    text-decoration: none;
    font-weight: bold;
  }

  .documentation-link:hover {
    text-decoration: underline;
  }

  .documentation-section {
    text-align: center;
    margin-top: 2rem;
  }

  .documentation-button {
    background-color: #333;
    color: #fff;
    padding: 1rem 1rem;
    font-size: 1.2rem;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }

  .documentation-button:hover {
    background-color: #444;
  }

  /* Estilo do ícone personalizado */
  .documentation-button i {
    font-size: 1.5rem;
  }

  .flip-card {
    background-color: transparent;
    width: 250px;
    height: 350px;
    perspective: 1000px;
    font-family: 'Arial', sans-serif;
    margin: 2rem;
    transition: transform 0.4s;
    cursor: pointer;
  }

  .flip-card:hover .flip-card-inner {
    transform: rotateY(180deg);
  }

  .flip-card-inner {
    position: relative;
    width: 100%;
    height: 100%;
    text-align: center;
    transition: transform 0.8s cubic-bezier(0.4, 0.2, 0.2, 1);
    transform-style: preserve-3d;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    border-radius: 1rem;
  }

  .flip-card-front,
  .flip-card-back {
    position: absolute;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    border-radius: 1rem;
    padding: 20px;
    box-shadow: inset 0 0 10px rgba(255, 0, 0, 0.3);
  }

  .flip-card-front {
    background: linear-gradient(145deg, #8b0000, #ff4d4d);
    color: #ffffff;
  }

  .flip-card-back {
    background: linear-gradient(145deg, #a00000, #ff3333);
    color: #ffffff;
    transform: rotateY(180deg);
  }

  .title {
    font-size: 1.2em;
    font-weight: bold;
    text-align: center;
    margin: 0;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  .flip-card-front hr,
  .flip-card-back hr {
    width: 60%;
    height: 2px;
    background-color: rgba(255, 220, 220, 0.7);
    border: none;
    margin: 10px 0;
  }

  .flip-card-front h2,
  .flip-card-back h2 {
    font-size: 1.4em;
    margin: 0;
    font-weight: 700;
  }

  .flip-card-front p,
  .flip-card-back p {
    font-size: 1em;
    margin: 10px 0;
    color: #ffdddd;
  }

  .flip-card-back .btn {
    display: inline-block;
    margin-top: 15px;
    padding: 8px 16px;
    font-size: 0.9em;
    font-weight: bold;
    color: #ffffff;
    background-color: #ff3333;
    border: none;
    border-radius: 5px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    transition: background 0.3s;
  }

  .flip-card-back .btn:hover {
    background-color: #ff0000;
  }

  @media (max-width: 768px) {
    main {
      margin-left: 0;
      padding-top: 100px;
      /* Espaço extra para a sidebar */
    }

    .services {
      display: flex;
      flex-direction: column;
      align-items: center;
      /* Ajusta para centralizar */
    }

    .flip-card {
      width: 150px;
      height: 200px;
      margin-top: 20px;
    }
  }

  .flip-card {
    background-color: transparent;
    width: 250px;
    height: 350px;
    perspective: 1000px;
    font-family: 'Arial', sans-serif;
    margin: 2rem;
    transition: transform 0.4s;
    cursor: pointer;
  }

  .flip-card:hover .flip-card-inner {
    transform: rotateY(180deg);
  }

  .flip-card-inner {
    position: relative;
    width: 100%;
    height: 100%;
    text-align: center;
    transition: transform 0.8s cubic-bezier(0.4, 0.2, 0.2, 1);
    transform-style: preserve-3d;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    border-radius: 1rem;
  }

  .flip-card-front,
  .flip-card-back {
    position: absolute;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    border-radius: 1rem;
    padding: 20px;
    box-shadow: inset 0 0 10px rgba(255, 0, 0, 0.3);
  }

  .flip-card-front {
    background: linear-gradient(145deg, #8b0000, #ff4d4d);
    color: #ffffff;
  }

  .flip-card-back {
    background: linear-gradient(145deg, #a00000, #ff3333);
    color: #ffffff;
    transform: rotateY(180deg);
  }

  .title {
    font-size: 1.2em;
    font-weight: bold;
    text-align: center;
    margin: 0;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  .flip-card-front hr,
  .flip-card-back hr {
    width: 60%;
    height: 2px;
    background-color: rgba(255, 220, 220, 0.7);
    border: none;
    margin: 10px 0;
  }

  .flip-card-front h2,
  .flip-card-back h2 {
    font-size: 1.4em;
    margin: 0;
    font-weight: 700;
  }

  .flip-card-front p,
  .flip-card-back p {
    font-size: 1em;
    margin: 10px 0;
    color: #ffdddd;
  }

  .flip-card-back .btn {
    display: inline-block;
    margin-top: 15px;
    padding: 8px 16px;
    font-size: 0.9em;
    font-weight: bold;
    color: #ffffff;
    background-color: #ff3333;
    border: none;
    border-radius: 5px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    transition: background 0.3s;
  }

  .flip-card-back .btn:hover {
    background-color: #ff0000;
  }



  @media (max-width: 1500px) {
    body {
      margin: 0px;
      /* Remove margens do body */
      overflow: hidden;
      /* Remove a rolagem */
    }

    main {
      height: 100vh;
      /* Faz com que o main ocupe toda a altura da viewport */
      margin-left: 10%;
      /* Mantém a margem esquerda */
      padding: 0;
      /* Remove o padding para que não haja espaço em volta */
    }

    .nr12-image {
      width: 80%;
      /* Mantém a largura da imagem */
      height: auto;
      /* Mantém a proporção */
      max-width: 1000px;
      /* Limite máximo */
      object-fit: cover;
      /* Garante que a imagem preencha o espaço */
      margin: 0;
      /* Remove margens para que a imagem fique colada ao topo */
      display: flex;
      /* Certifica que a imagem se comporte como um bloco */
    }

    h2,
    h5 {
      margin-top: 0;
      /* Remove a margem superior */
      padding-top: 0;
      /* Remove qualquer padding superior */
    }

    .flip-card {
      background-color: transparent;
      width: 170px;
      /* Largura reduzida dos cards */
      height: 220px;
      /* Altura reduzida dos cards */
      margin-top: 20px;
    }

  }

  /* css pra notebook */
  @media (max-width: 992px) {
    .nr12_image {
      margin-left: 60px;
      /* Reduz a margem esquerda */
    }

    h2,
    h5 {
      padding-left: 4rem;
      /* Reduz o padding esquerdo */
      font-size: 30px;
      /* Diminui o tamanho da fonte */
    }

    .flip-card {
      width: 200px;
      /* Largura para telas medianas */
      height: 260px;
      /* Altura para telas medianas */

    }
  }



  /* Media query para celulares (em torno de 600px) */
  @media (max-width: 768px) {
    .nr12_image {
      display: none;
      /* Remove a largura máxima */

    }

    .nr12-image-container {
      margin-top: 1px;

    }

    main {
      margin: 0;
      /* Zera a margem esquerda */
    }

    h2,
    h5 {
      font-size: 20px;
      /* Tamanho de fonte menor para celular */
      padding-left: 20px;
      /* Padding esquerdo menor para celular*/
      margin-top: 2px;
      text-align: justify;


    }

    h2 {
      text-align: left;
      /* Ou centralizado, se preferir */
    }

    h5 {

      text-align: justify;
      max-width: 80%;
    }

    p {
      max-width: 90%;
      /* Largura do parágrafo ocupando quase toda a tela */
      margin-left: auto;
      /* Centraliza o parágrafo */
      margin-right: auto;
      line-height: 1.0;
      /* Espaçamento entre linhas para celular */
    }

    .services {
      display: none;
      flex-wrap: wrap;
      /* Permite quebra automática das caixas */
      justify-content: center;
      align-items: center;
      /* Centraliza verticalmente os itens*/

    }

    .service {
      width: 40%;
      /* Largura para cada serviço no mobile*/
      height: auto;
      /* Deixa a altura ajustar automaticamente ao conteúdo */
      margin: 10px;
      /* Adiciona margem */
    }

    .flip-card {
      width: 150px;
      height: 200px;
      margin-top: 4px;
      /* Largura ainda mais reduzida dos cards */
    }

    .flip-card-front,
    .flip-card-back {
      width: 100%;
      /* Define a largura como 100% */
      height: 100%;
      /* Define a altura como 100% */
    }

    .intro {
      display: flex;
      /* Muda o display */
      flex-direction: column;
      /* Alinha os elementos verticalmente */
      align-items: center;
      /* Centraliza horizontalmente */
    }

    body {
      overflow-x: hidden;
      /* Impede a rolagem horizontal no celular */
    }

  }
</style>
<?php
include './conexao.php';

$dataAtual = date('Y-m-d');

// Consultar máquinas com manutenção próxima, incluindo a data atual
$queryProximas = $pdo->prepare("
    SELECT idmaquina, data_proxima_manutencao 
    FROM maquina 
    WHERE DATEDIFF(data_proxima_manutencao, :dataAtual) <= 10 AND DATEDIFF(data_proxima_manutencao, :dataAtual) >= 0
");
$queryProximas->execute(['dataAtual' => $dataAtual]);
$manutencoesProximas = $queryProximas->fetchAll(PDO::FETCH_ASSOC);

// Consultar máquinas com manutenção atrasada
$queryVencidas = $pdo->prepare("
    SELECT idmaquina, data_proxima_manutencao 
    FROM maquina 
    WHERE data_proxima_manutencao < :dataAtual
");
$queryVencidas->execute(['dataAtual' => $dataAtual]);
$manutencoesVencidas = $queryVencidas->fetchAll(PDO::FETCH_ASSOC);

// Total de notificações
$totalNotificacoes = count($manutencoesProximas) + count($manutencoesVencidas);
?>


<style>
  .notification {
    position: fixed;
    top: 20px;
    right: 20px;
    cursor: pointer;
  }

  .notification img {
    display: block;
    margin: 0 auto;
  }

  .notification-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    background-color: #e74c3c;
    /* Vermelho para chamar atenção */
    color: white;
    font-size: 12px;
    font-weight: bold;
    border-radius: 50%;
    padding: 5px 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.3);
  }

  .modal {
    display: none;
    position: fixed;
    z-index: 999;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 500px;
    background-color: #ffffff;
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    text-align: center;
    color: #333;
  }

  .modal.active {
    display: block;
  }

  .modal-header {
    font-size: 20px;
    font-weight: bold;
    color: #000;
    margin-bottom: 15px;
  }

  .modal-close {
    text-align: right;
    cursor: pointer;
    font-weight: bold;
    color: #888;
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 18px;
  }

  .modal-close:hover {
    color: #000;
  }

  .modal-body h4 {
    font-size: 18px;
    color: #444;
    margin: 10px 0;
    text-decoration: underline;
  }

  .modal-body ul {
    list-style-type: none;
    padding: 0;
  }

  .modal-body li {
    font-size: 16px;
    margin: 5px 0;
    padding: 5px 0;
    border-bottom: 1px dashed #ddd;
    color: #555;
  }
  p{
    color: #000;
    font-size: 20px;
  }

  .modal-body p {
    font-size: 16px;
    color: #777;
  }

  .mensagem-manu {
    color: #333;
  }
  .intro{
    font-size: 26px;
    margin-top: 40px;
  }

  
  @media (max-width: 550px) {
    .notification img {
      width: 40px;
    }

    .notification-badge {
      width: 10px;
      height: 18px;
    }
    body {
    overflow-x: hidden; /* Impede a rolagem horizontal */
    overflow-y: auto; /* Permite a rolagem vertical */
    margin: 0;
    padding: 0;
}
.intro{
    font-size: 20px;
  }

  }
 
</style>

<body>
  <!-- Sino de notificação -->
  <div class="notification" onclick="toggleModal()">
    <img src="./imagem/sino.png" alt="Notificação de Manutenção" width="50">
    <?php if ($totalNotificacoes > 0): ?>
      <span class="notification-badge" id="notification-badge"><?= $totalNotificacoes ?></span>
    <?php endif; ?>
  </div>

  <!-- Modal de Notificações -->
  <div id="modal" class="modal">
    <div class="modal-close" onclick="toggleModal()">✖</div>
    <div class="modal-header">Notificações de Manutenção</div>
    <div class="modal-body">
      <?php if (count($manutencoesProximas) > 0 || count($manutencoesVencidas) > 0): ?>
        <?php if (count($manutencoesVencidas) > 0): ?>
          <h4>Manutenções Vencidas:</h4>
          <ul>
            <?php foreach ($manutencoesVencidas as $manutencao): ?>
              <li>Máquina ID: <?= htmlspecialchars($manutencao['idmaquina']) ?> - Vencida desde
                <?= htmlspecialchars($manutencao['data_proxima_manutencao']) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php if (count($manutencoesProximas) > 0): ?>
          <h4>Manutenções Próximas:</h4>
          <ul>
            <?php foreach ($manutencoesProximas as $manutencao): ?>
              <li>Máquina ID: <?= htmlspecialchars($manutencao['idmaquina']) ?> - Prevista para
                <?= htmlspecialchars($manutencao['data_proxima_manutencao']) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      <?php else: ?>
        <p>Nenhuma manutenção pendente ou vencida.</p>
      <?php endif; ?>
    </div>
  </div>

  <script>
    function toggleModal() {
      const modal = document.getElementById('modal');
      modal.classList.toggle('active');
    }

    function atualizarNotificacoes() {
      const xhr = new XMLHttpRequest();
      xhr.open('GET', '<?= $_SERVER['PHP_SELF'] ?>?atualizar_notificacoes=1', true);
      xhr.onload = function () {
        if (xhr.status === 200) {
          const data = JSON.parse(xhr.responseText);
          const badge = document.getElementById('notification-badge');
          if (data.totalNotificacoes > 0) {
            badge.textContent = data.totalNotificacoes;
            badge.style.display = 'flex';
          } else {
            badge.style.display = 'none';
          }
        }
      };
      xhr.send();
    }

    setInterval(atualizarNotificacoes, 1000); // Atualizar a cada 30 segundos
    document.addEventListener('DOMContentLoaded', function () {
      atualizarNotificacoes();
    });
  </script>
</body>

</html>

<?php
if (isset($_GET['atualizar_notificacoes'])) {
  echo json_encode(['totalNotificacoes' => $totalNotificacoes]);
  exit;
}
?>



<main>
  <!-- Contêiner da imagem grande do NR12 -->
  <div class="nr12-image-container">
    <img src="imagem/Slide_NR12.png" alt="NR12" class="nr12_image" height="270">
  </div>

  <section class="intro">
    <h2>Sobre a Norma NR12</h2>
      NR-12 é uma Norma Regulamentadora (NR) que estabelece requisitos mínimos de segurança no trabalho com
      máquinas e equipamentos. A NR-12 é obrigatória para organizações e órgãos públicos que utilizem máquinas
      e equipamentos e tenham empregados regidos pela Consolidação das Leis do Trabalho.
  </section>
  <br>
  <section class="intro">
  <h2>Bem-vindo ao Sistema NR12</h2>
      Este sistema foi desenvolvido para otimizar o processo de realização de checklists de máquinas, atendendo à norma
      NR12. Substituímos os formulários em papel por uma solução digital prática e eficiente, especialmente projetada
      para os alunos do SENAI.
      Para realizar o checklist antes de utilizar uma máquina, basta acessar o sistema com sua matrícula e NI. Tudo foi
      pensado para tornar o processo mais simples e garantir a segurança no ambiente de aprendizado.
  </section>


</main>

<script>
  // Função para alternar o modal
  function toggleModal() {
    const modal = document.getElementById('modal');
    modal.classList.toggle('active');
  }
</script>
</body>

</html>