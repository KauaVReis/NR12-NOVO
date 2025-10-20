<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Verifica se a constante BASE_URL já foi definida
if (!defined('BASE_URL')) {
    // Obtém o diretório base do script atual
    $base_dir = dirname($_SERVER['SCRIPT_NAME']);
    // Adiciona uma barra no final se não houver
    $base_dir = rtrim($base_dir, '/') . '/';
    // Define a constante BASE_URL
    define('BASE_URL', $base_dir);
}
// Verifica o nível de acesso
$user_role = $_SESSION['colaborador_permissao'] ?? 'guest'; // Se não houver, define como 'guest'

if($user_role=='guest'){
    header('location: ../Login/login.php');
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NR12 Senai</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/estilos.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar Toggle Button -->
    <div id="menuToggle" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
        <span class="menu-icon"></span>
        <span class="menu-icon"></span>
        <span class="menu-icon"></span>
    </div>
    <!-- Sidebar -->
    <div id="mySidebar" class="sidebar_sidebar">
        <i id="closeIcon" class="fa-solid fa-x" style="display: none;" onclick="toggleSidebar()"></i>
        <!-- Ícone de X adicionado aqui -->
        <div class="logo-container_sidebar">
            <a href="<?php echo BASE_URL; ?>home.php" style="background: none; border: none;">
                <img class="logo_sidebar" src="<?php echo BASE_URL; ?>imagem/senailogo.png" alt="Logo da Senai">
            </a>
        </div>
        <!-- <div class="user-info_sidebar">
            <div class="user-name_sidebar"><?php echo htmlspecialchars($_SESSION['colaborador_nome']); ?></div>
        </div> -->
        <!-- Menu de Navegação -->
        <a href="<?php echo BASE_URL; ?>home.php" class="Btn_sidebar">
            <span class="material-symbols-outlined">quick_reference_all</span>
            <div class="text_sidebar">Painel</div>
        </a>
        <!-- Dropdown Cursos -->
        <?php if ($user_role === 'Adm' || $user_role === 'Coordenador'): ?>
        <div class="dropdown_sidebar">
            <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownCursos', this)" aria-expanded="false">
                <span class="material-symbols-outlined">school</span>
                <div class="text_sidebar">Cursos<span class="arrow">▼</span></div>
            </a>
            <div id="dropdownCursos" class="dropdown-content_sidebar">
                <a href="cursos/cadastroCurso.php#">Cadastrar Curso</a>
                <a href="cursos/consultaCurso.php#">Consultar Cursos</a>
            </div>
        </div>
        <!-- Dropdown Turmas -->
        <div class="dropdown_sidebar">
            <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownTurmas', this)" aria-expanded="false">
                <span class="material-symbols-outlined">groups</span>
                <div class="text_sidebar">Turmas<span class="arrow">▼</span></div>
            </a>
            <div id="dropdownTurmas" class="dropdown-content_sidebar">
                <a href="turmas/cadastro.php#">Cadastrar Turmas</a>
                <a href="turmas/consulta.php#">Consultar Turmas</a>
            </div>
        </div>
        <!-- Dropdown Alunos -->
        <div class="dropdown_sidebar">
            <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownAlunos', this)" aria-expanded="false">
                <span class="material-symbols-outlined">class</span>
                <div class="text_sidebar">Alunos <span class="arrow">▼</span></div>
            </a>
            <div id="dropdownAlunos" class="dropdown-content_sidebar">
                <a href="alunos/cadastro.php#">Cadastrar Aluno</a>
                <a href="alunos/consulta.php#">Consultar Alunos</a>
            </div>
        </div>
        <?php endif; ?>
        <!-- Verifica o nível de acesso e exibe o conteúdo da sidebar -->
        <?php if ($user_role === 'Adm' || $user_role === 'Coordenador'): ?>
            <!-- Dropdown Funcionários -->
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownUnidades', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">apartment</span> <!-- Ícone atualizado -->
                    <div class="text_sidebar">Unidade <span class="arrow">▼</span></div>
                </a>
                <div id="dropdownUnidades" class="dropdown-content_sidebar">
                    <a href="unidade/cadastro.php#">Cadastrar Unidade</a>
                    <a href="unidade/consulta.php#">Consultar Unidade</a>
                </div>
            </div>
            <!-- Dropdown Setor -->
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownSetor', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">business</span>
                    <div class="text_sidebar">Setor <span class="arrow">▼</span></div>
                </a>
                <div id="dropdownSetor" class="dropdown-content_sidebar">
                    <a href="setor/cadastro.php#">Cadastrar Setor</a>
                    <a href="setor/consulta.php#">Consultar Setor</a>
                </div>
            </div>
            <!-- Dropdown Funcionários -->
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownFuncionarios', this)"
                    aria-expanded="false">
                    <span class="material-symbols-outlined">business_center</span>
                    <div class="text_sidebar">Funcionários <span class="arrow">▼</span></div>
                </a>
                <div id="dropdownFuncionarios" class="dropdown-content_sidebar">
                    <a href="colaboradores/cadastro.php#">Cadastrar Funcionários</a>
                    <a href="colaboradores/consulta.php#">Consultar Funcionários</a>
                </div>
            </div>
            <!-- Dropdown Motor -->
            <?php if ($user_role === 'Adm' || $user_role === 'Professor' || $user_role === 'Coordenador'): ?>
                <div class="dropdown_sidebar">
                    <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownMotor', this)" aria-expanded="false">
                        <span class="material-symbols-outlined">precision_manufacturing</span>
                        <div class="text_sidebar">Motores<span class="arrow">▼</span></div>
                    </a>
                    <div id="dropdownMotor" class="dropdown-content_sidebar">
                        <a href="motor/cadastro.php#">Cadastrar Motor</a>
                        <a href="motor/consulta.php#">Consultar Motor</a>
                    </div>
                </div>
            <?php endif; ?>
            <!-- Dropdown Máquinas -->
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownMaquinas', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">settings</span>
                    <div class="text_sidebar">Máquinas<span class="arrow">▼</span></div>
                </a>
                <div id="dropdownMaquinas" class="dropdown-content_sidebar">
                    <a href="maquinas/cadastro.php#">Cadastrar Máquina</a>
                    <a href="maquinas/consulta.php#">Consultar Máquinas</a>
                    <a href="tipo_maquinas/cadastro.php#">Cadastrar Tipo de Máquinas</a>
                    <a href="tipo_maquinas/consulta.php#">Consultar tipo de maquina</a>
                    <a href="tipo_maquinas/tipomaquina_requisito.php#">Relacionar requisitos a maquina</a>
                </div>
            </div>
            <!-- Dropdown Requisitos -->
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownRequisitos', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">edit_document</span>
                    <div class="text_sidebar">Requisitos<span class="arrow">▼</span></div>
                </a>
                <div id="dropdownRequisitos" class="dropdown-content_sidebar">
                    <a href="requisitos/cadastro.php#">Cadastrar Requisitos</a>
                    <a href="requisitos/consultar.php#">Consultar Requisitos</a>
                </div>
            </div>
            <!-- Dropdown Manutenção -->
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownManutencao', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">build</span>
                    <div class="text_sidebar">Manutenção <span class="arrow">▼</span></div>
                </a>
                <div id="dropdownManutencao" class="dropdown-content_sidebar">
                    <a href="manutencao/cadastrar.php#">Cadastrar Manutenção</a>
                    <a href="manutencao/tabela.php#">Consulta de Manutenção</a>
                    <a href="manutencao/maquinas_defeito.php#">Relatório de Manutenção por Aluno</a>
                    <a href="manutencao/consultar_manutencao.php#">Proximas manutenções</a>
                </div>
            </div>
            <!-- Dropdown Logs -->
            <a href="<?php echo BASE_URL; ?>historico/consulta.php#" class="Btn_sidebar">
                <span class="material-symbols-outlined">monitoring</span>
                <div class="text_sidebar">Exibir Logs</div>
            </a>
            <a href="<?php echo BASE_URL; ?>documentacao/documentacao.php#" class="Btn_sidebar">
                <span class="material-symbols-outlined">
                    description
                </span>
                <div class="text_sidebar">Documentação</div>
            </a>
        <?php endif; ?>
        <?php if ($user_role === 'Professor'): ?>
            <!-- Dropdown Turmas -->
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownTurmas', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">groups</span>
                    <div class="text_sidebar">Turmas<span class="arrow">▼</span></div>
                </a>
                <div id="dropdownTurmas" class="dropdown-content_sidebar">
                    <a href="turmas/cadastro.php#">Cadastrar Turmas</a>
                    <a href="turmas/consulta.php#">Consultar Turmas</a>
                </div>
            </div>
            <!-- Dropdown Alunos -->
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownTurmasAlunos', this)"
                    aria-expanded="false">
                    <span class="material-symbols-outlined">groups</span>
                    <div class="text_sidebar">Alunos <span class="arrow">▼</span></div>
                </a>
                <div id="dropdownTurmasAlunos" class="dropdown-content_sidebar">
                    <a href="alunos/cadastro.php#">Cadastrar Aluno</a>
                    <a href="alunos/consulta.php#">Consultar Alunos</a>
                </div>
            </div>
            <!-- Dropdown Máquinas -->
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownMaquinas', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">settings</span>
                    <div class="text_sidebar">Máquinas <span class="arrow">▼</span></div>
                </a>
                <div id="dropdownMaquinas" class="dropdown-content_sidebar">
                    <a href="maquinas/cadastro.php#">Cadastrar Máquina</a>
                    <a href="maquinas/consulta.php#">Consultar Máquinas</a>
                    <a href="tipo_maquinas/cadastro.php#">Cadastrar Tipo de Máquinas</a>
                    <a href="tipo_maquinas/tipomaquina_requisito.php#">Relacionar requisitos a maquina</a>
                </div>
            </div>
            <!-- Dropdown Manutenção -->
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownManutencao', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">build</span>
                    <div class="text_sidebar">Manutenção <span class="arrow">▼</span></div>
                </a>
                <div id="dropdownManutencao" class="dropdown-content_sidebar">
                    <a href="manutencao/cadastrar.php#">Cadastrar Manutenção</a>
                    <a href="manutencao/tabela.php#">Consulta de Manutenção</a>
                    <a href="manutencao/maquinas_defeito.php#">Consulta de Manutenção Aluno</a>
                    <a href="manutencao/consultar_manutencao.php#">Proximas manutenções</a>
                </div>
            </div>
            <!-- Dropdown Logs -->
            <a href="historico/consulta.php#" class="Btn_sidebar">
                <span class="material-symbols-outlined">monitoring</span>
                <div class="text_sidebar">Exibir Logs</div>
            </a>
            <a href="<?php echo BASE_URL; ?>Documentacao/documentacao.php" class="Btn_sidebar">
                <span class="material-symbols-outlined">
                    description
                </span>
                <div class="text_sidebar">Documentação</div>
            </a>
        <?php endif; ?>
        <!-- Dropdown Suporte -->
        <?php if ($user_role === 'Adm' || $user_role === 'Professor' || $user_role === 'Coordenador'): ?>
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownErro', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">support_agent</span>
                    <div class="text_sidebar">Suporte<span class="arrow">▼</span></div>
                </a>
                <div id="dropdownErro" class="dropdown-content_sidebar">
                    <a href="erro/cadastrar.php#">Enviar Mensagem</a>
                    <a href="erro/consultar.php#">Consultar Mensagem</a>
                </div>
            </div>
        <?php endif; ?>
        <!-- Dropdown Perfil -->
        <?php if ($user_role === 'Adm' || $user_role === 'Professor' || $user_role === 'Coordenador'): ?>
            <!-- Menu para todos -->
            <a href="<?php echo BASE_URL; ?>Perfil/perfil.php#" class="Btn_sidebar">
                <span class="material-symbols-outlined">person</span>
                <div class="text_sidebar">Perfil</div>
            </a>
        <?php endif; ?>
        <?php if ($user_role === 'Manutencao' ): ?>
            <!-- Dropdown Máquinas -->
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownMaquinas', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">settings</span>
                    <div class="text_sidebar">Máquinas <span class="arrow">▼</span></div>
                </a>
                <div id="dropdownMaquinas" class="dropdown-content_sidebar">
                    <a href="maquinas/consulta.php#">Consultar Máquinas</a>
                </div>
            </div>
            <!-- Dropdown Manutenção -->
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownManutencao', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">build</span>
                    <div class="text_sidebar">Manutenção <span class="arrow">▼</span></div>
                </a>
                <div id="dropdownManutencao" class="dropdown-content_sidebar">
                    <a href="manutencao/cadastrar.php#">Cadastrar Manutenção</a>
                    <a href="manutencao/tabela.php#">Consulta de Manutenção</a>
                    <a href="manutencao/maquinas_defeito.php#">Consulta de Manutenção Aluno</a>
                    <a href="manutencao/consultar_manutencao.php#">Proximas manutenções</a>
                </div>
            </div>
            <!-- Dropdown Suporte -->
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownErro', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">support_agent</span>
                    <div class="text_sidebar">Suporte<span class="arrow">▼</span></div>
                </a>
                <div id="dropdownErro" class="dropdown-content_sidebar">
                    <a href="erro/cadastrar.php#">Enviar Mensagem</a>
                    <a href="erro/consultar.php#">Consultar Mensagem</a>
                </div>
            </div>
            <a href="<?php echo BASE_URL; ?>Perfil/perfil.php#" class="Btn_sidebar">
                <span class="material-symbols-outlined">person</span>
                <div class="text_sidebar">Perfil</div>
            </a>

        <?php endif; ?>
        <!-- Botão Sair -->
        <div class="container_botaosair">
            <button class="botaosair" onclick="window.location.href='<?php echo BASE_URL; ?>logout.php'">
                <span class="material-symbols-outlined">logout</span>
                <span>Sair</span>
            </button>
        </div>
    </div>
    <!-- JavaScript para controlar a sidebar e dropdown -->
    <script>
        function toggleDropdown(dropdownId, element) {
            var dropdownContent = document.getElementById(dropdownId);
            dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
            var isExpanded = element.getAttribute('aria-expanded') === 'true';
            element.setAttribute('aria-expanded', !isExpanded);

            // Altera o símbolo
            var arrow = element.querySelector('.arrow');
            arrow.textContent = dropdownContent.style.display === 'block' ? '▲' : '▼';

            dropdownContent.classList.toggle('active');
        }
        function toggleSidebar() {
            const sidebar = document.getElementById("mySidebar");
            const menuIcons = document.querySelectorAll(".menu-icon");
            const closeIcon = document.getElementById("closeIcon");

            sidebar.classList.toggle("active");

            if (sidebar.classList.contains("active")) {
                // Oculta o ícone de menu e exibe o ícone de fechar
                menuIcons.forEach(icon => icon.style.display = "none");
                closeIcon.style.display = "inline"; // Exibe o ícone "X"
            } else {
                // Exibe o ícone de menu e oculta o ícone de fechar
                menuIcons.forEach(icon => icon.style.display = "block");
                closeIcon.style.display = "none"; // Oculta o ícone "X"
            }
        }
    </script>
</body>

</html>