<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_dir = rtrim($base_dir, '/') . '/';
// define('BASE_URL', '../../nr12/');

$cadastroSucesso = false; // Variável para indicar se o cadastro foi bem-sucedido


// Processa o envio do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  include '../conexao.php';

  $maquina_id = $_POST['maquina_id'];
  $tipo_manutencao = isset($_POST['tipo_manutencao']) ? 1 : 0;
  $descricao = $_POST['manutencao_descricao'] ?? '';
  $data_manutencao = $_POST['data_manutencao'] ?? '';

  // Insere os dados na tabela de manutenção
  $query = "INSERT INTO manutencao (maquina_id, tipo_manutencao, manutencao_descricao, data_manutencao) VALUES (?, ?, ?, ?)";
  $stmt = $pdo->prepare($query);

  if ($stmt->execute([$maquina_id, $tipo_manutencao, $descricao, $data_manutencao])) {
    $cadastroSucesso = true;
  }
}
?>

<?php include __DIR__ . '/../sidebar.php'; ?>
<style>

body{
  height: auto;
}
.box-text_manu {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 1em;
}

#descricao-container {
  width: 70%;
  display: flex;
  flex-direction: column;
  align-items: center;
}
label[for="descricao"] {
  text-align: center; /* Centraliza o texto do label */
  margin-bottom: 10px; /* Espaço entre o label e o campo de texto */
}
form {
  width: 500px;
  padding: 25px;
}

  h1 {
    color: black;
    text-align: center;
  }


  @media (max-width: 540px) {
    .form-manut {
      width: 100%;
      max-width: 350px;
    }
    .checkbox_manu {
   background-color: white;
  }
  .Pdf-ajuda{
    width: 350px;

  }
  .text_manu{
    margin: auto;
  }
  
  .container-cadastro-manutencao {
    margin-top: 20%;
  }
  #descricao-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

label[for="descricao"] {
  text-align: center; /* Centraliza o texto do label */
  margin-bottom: 10px; /* Espaço entre o label e o campo de texto */
}
  }
  label[for="data_manutencao"] {
    display: flex;
  text-align: center; /* Centraliza o texto do label */
  width: 185px;
}
  .checkbox_manu:checked::after {
    display: flex;
    align-items: center;
    position: static;
    justify-content: center;
  }
  

  .checkbox_manu {
    appearance: none;
    width: 48px;
    height: 34px;
    border: 2px solid #bbb;
    border-radius: 25%;
    cursor: pointer;
    position: static;
    background-color: white;
    transition: background-color 0.3s ease, border-color 0.3s ease;
    margin-top: 20px;
  }

  .Pdf-ajuda {
    text-align: center;
  }

  .Pdf-ajuda a {
    display: inline-block;
    margin: 8px 0;
    padding: 10px 20px;
    color: #ffffff;
    background-color: #e21616;
    text-decoration: none;
    font-weight: bold;
    border-radius: 6px;
    transition: background-color 0.3s, box-shadow 0.3s;
    text-transform: uppercase;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  }

  .Pdf-ajuda a:hover {
    background-color: #fd2020;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    transform: translateY(-2px);
  }

  .modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }

  .modal {
    background: #fff;
    color: #a20b0b;
    width: 400px;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    animation: fadeIn 0.3s ease;
  }

  .modal h2 {
    color: #a20b0b;
  }

  .modal p {
    margin: 15px 0;
    font-size: 1.1em;
  }

  .modal button {
    background-color: #d41414;
    color: #fff;
    border: none;
    padding: 10px 20px;
    font-size: 1em;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.2s;
  }

  .modal button:hover {
    background-color: #e61f1f;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: scale(0.9);
    }

    to {
      opacity: 1;
      transform: scale(1);
    }
  }

  .modal-overlay {
    display: flex;
    /* Define como flex para exibir o modal */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }

  .modal {
    background: #fff;
    color: #a20b0b;
    width: 400px;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    animation: fadeIn 0.3s ease;
  }

  .modal h2 {
    color: #a20b0b;
  }

  .modal p {
    margin: 15px 0;
    font-size: 1.1em;
  }

  .modal button {
    background-color: #d41414;
    color: #fff;
    border: none;
    padding: 10px 20px;
    font-size: 1em;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.2s;
  }

  .modal button:hover {
    background-color: #e61f1f;
  }
  .box-check_manu{
    align-items: center;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: scale(0.9);
    }

    to {
      opacity: 1;
      transform: scale(1);
    }
  }
</style>

<body>
  <div class="container-cadastro-manutencao">
    <h1>Cadastrar Manutenção</h1>

    <form action="cadastro.php" method="POST" class="form-manut">
      <!-- Campos do formulário de cadastro -->
      <label for="pesquisa_ni">Pesquisar NI da Máquina:</label>

      <input type="text" id="pesquisa_ni" placeholder="Digite o NI para filtrar">

      <label for="maquina">Selecione a Máquina (NI):</label>
      <select name="maquina_id" id="maquina" required>
            <option value="" disabled selected>Selecione o NI</option>
            <?php
            include '../conexao.php';
            require_once '../verifica_permissao.php';
            verificaPermissao(['Adm', 'Coordenador', 'Professor', 'Manutencao']);
            
            // Verifica se o NI foi passado via GET
            $niSelecionado = isset($_GET['maquina_ni']) ? $_GET['maquina_ni'] : null;

            // Consulta ao banco de dados
            $query = "SELECT idmaquina, maquina_ni FROM maquina";
            $stmt = $pdo->query($query);

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $idmaquina = $row['idmaquina'];
                $maquina_ni = $row['maquina_ni'];

                // Verifica se este NI deve ser marcado como selecionado
                $selected = ($niSelecionado === $maquina_ni) ? 'selected' : '';

                echo "<option value='$idmaquina' $selected>$maquina_ni</option>";
            }
            ?>
        </select>

      <div class="sub-container_manu">
        <div class="box-check_manu">
          <input type="checkbox" name="tipo_manutencao" id="tipo_manutencao" class="checkbox_manu">
          <label for="tipo_manutencao" style="margin-left: 8px;">Manutenção Preventiva</label>
        </div>

        <div id="descricao-container" class="box-text_manu">
          <label for="descricao">Descrição do <br> Problema:</label>
          <textarea name="manutencao_descricao" id="descricao" class="text_manu" oninput="ajustarAltura(this)"
            rows="2"></textarea>
        </div>

        <div id="data-container" style="display: none;">
          <label for="data_manutencao">Data da Manutenção:</label>
          <input type="date" name="data_manutencao" id="data_manutencao">
        </div>
      </div>

      <input type="submit" value="Cadastrar Manutenção">
    </form>

    <div class="Pdf-ajuda">
      <a href="../ajuda/CADASTRAR MANUTENÇÃO.pdf" target="_blank">Está com dificuldade de cadastrar uma Manutenção?
        Clique aqui.</a>
    </div>
  </div>

  <!-- Modal de sucesso -->
  <?php if ($cadastroSucesso): ?>
    <div id="modalOverlay" class="modal-overlay">
      <div class="modal">
        <h2>Sucesso!</h2>
        <p>A manutenção foi cadastrada com sucesso!</p>
        <button onclick="closeModal()">Fechar</button>
      </div>
    </div>
  <?php endif; ?>

  <script>
    // Função para fechar o modal e redirecionar após o cadastro

    // Exibe o modal apenas se o cadastro foi bem-sucedido
    window.onload = function () {
      <?php if ($cadastroSucesso): ?>
        document.getElementById('modalOverlay').style.display = 'flex';
      <?php endif; ?>
    };
    function closeModal() {
      const modal = document.getElementById('modalOverlay');
      modal.style.display = 'none'; // Oculta o modal
      window.location.href = 'consulta.php'; // Redireciona para a página de consulta
    }

    // Função para pesquisa no campo de seleção
    document.getElementById('pesquisa_ni').addEventListener('input', function () {
      const filter = this.value.toUpperCase();
      const select = document.getElementById('maquina');
      const options = select.getElementsByTagName('option');

      for (let i = 1; i < options.length; i++) {
        const txtValue = options[i].textContent || options[i].innerText;
        options[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? '' : 'none';
      }
    });

    // Exibe ou oculta os campos conforme o checkbox é marcado
    document.getElementById('tipo_manutencao').addEventListener('change', function () {
      const descricaoContainer = document.getElementById('descricao-container');
      const dataContainer = document.getElementById('data-container');

      if (this.checked) {
        descricaoContainer.style.display = 'none';
        dataContainer.style.display = 'block';
      } else {
        descricaoContainer.style.display = 'block';
        dataContainer.style.display = 'none';
      }
    });

    // Ajusta a altura do textarea conforme o conteúdo
    function ajustarAltura(elemento) {
      elemento.style.height = "auto";
      elemento.style.height = elemento.scrollHeight + "px";
    }
    // Exibe ou oculta os campos conforme o checkbox é marcado
document.getElementById('tipo_manutencao').addEventListener('change', function () {
  const descricaoContainer = document.getElementById('descricao-container');
  const dataContainer = document.getElementById('data-container');
  const labelDescricao = document.querySelector('label[for="descricao"]');

  if (this.checked) {
    descricaoContainer.style.display = 'none';
    dataContainer.style.display = 'block';
    // Modifica o label para 'Data da Manutenção' quando o checkbox está marcado
    labelDescricao.innerHTML = 'Data da Manutenção:';
  } else {
    descricaoContainer.style.display = 'block';
    dataContainer.style.display = 'none';
    // Restaura o label original quando o checkbox é desmarcado
    labelDescricao.innerHTML = 'Descrição do <br> Problema:';
  }
});

  </script>
</body>