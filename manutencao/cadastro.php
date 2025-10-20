<?php
// Obtém o diretório base do servidor
$base_dir = dirname($_SERVER['SCRIPT_NAME']);

// Adiciona uma barra no final se não houver
$base_dir = rtrim($base_dir, '/') . '/';

// Corrige a URL para sempre começar do diretório raiz do projeto
define('BASE_URL', '../../nr12/');
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/nr12/sidebar.php'; ?>

<?php
include '../conexao.php'; // Inclui a conexão PDO

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Obter dados do formulário
  $maquina_id = $_POST['maquina_id'];
  $colaborador_id = $_SESSION['user_id'];
  $tipo_manutencao = isset($_POST['tipo_manutencao']) ? 'Preventiva' : 'Corretiva';

  if (!$colaborador_id) {
    echo "Erro: colaborador não está logado.";
    exit;
  }

  $status = 'Quebrado';

  // Verificar tipo de manutenção
  if ($tipo_manutencao == 'Preventiva') {
    $data_manutencao = $_POST['data_manutencao'];
    $descricao = null; // Não necessita de descrição
  } else {
    $data_manutencao = date('Y-m-d H:i:s'); // Data atual para manutenção corretiva
    $descricao = $_POST['manutencao_descricao'];
  }

  // Inserir na tabela `manutencao`
  $sql = "INSERT INTO manutencao (manutencao_data, maquina_id, colaborador_id, manutencao_descricao, tipo_manutencao)
          VALUES (:data, :maquina_id, :colaborador_id, :descricao, :tipo)";
  $stmt = $pdo->prepare($sql);

  // Vincular parâmetros
  $stmt->bindParam(':data', $data_manutencao);
  $stmt->bindParam(':maquina_id', $maquina_id);
  $stmt->bindParam(':colaborador_id', $colaborador_id);
  $stmt->bindParam(':descricao', $descricao);
  $stmt->bindParam(':tipo', $tipo_manutencao);

  // Executa a query
  if ($stmt->execute()) {
  } else {
  }
}
?>
<meta http-equiv="refresh" content="0; URL='tabela.php'">