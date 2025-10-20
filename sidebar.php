<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Inclui o arquivo de configuração central
// Esta linha busca o config.php a partir da pasta raiz do projeto
require_once __DIR__ . '/config.php';

// Verifica o nível de acesso
$user_role = $_SESSION['colaborador_permissao'] ?? 'guest';

if ($user_role == 'guest') {
    // O header também deve usar a BASE_URL para ser consistente
    header('location: ' . BASE_URL . 'Login/login.php');
    exit(); // É uma boa prática adicionar exit() após um redirecionamento
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
    <div id="menuToggle" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
        <span class="menu-icon"></span>
        <span class="menu-icon"></span>
        <span class="menu-icon"></span>
    </div>
    <div id="mySidebar" class="sidebar_sidebar">
        <i id="closeIcon" class="fa-solid fa-x" style="display: none;" onclick="toggleSidebar()"></i>
        <div class="logo-container_sidebar">
            <a href="<?php echo BASE_URL; ?>home.php" style="background: none; border: none;">
                <img class="logo_sidebar" src="<?php echo BASE_URL; ?>imagem/senailogo.png" alt="Logo da Senai">
            </a>
        </div>
        <a href="<?php echo BASE_URL; ?>home.php" class="Btn_sidebar">
            <span class="material-symbols-outlined">quick_reference_all</span>
            <div class="text_sidebar">Painel</div>
        </a>

        <?php if ($user_role === 'Adm' || $user_role === 'Coordenador') : ?>
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownCursos', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">school</span>
                    <div class="text_sidebar">Cursos<span class="arrow">▼</span></div>
                </a>
                <div id="dropdownCursos" class="dropdown-content_sidebar">
                    <a href="<?php echo BASE_URL; ?>cursos/cadastroCurso.php#">Cadastrar Curso</a>
                    <a href="<?php echo BASE_URL; ?>cursos/consultaCurso.php#">Consultar Cursos</a>
                </div>
            </div>
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownTurmas', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">groups</span>
                    <div class="text_sidebar">Turmas<span class="arrow">▼</span></div>
                </a>
                <div id="dropdownTurmas" class="dropdown-content_sidebar">
                    <a href="<?php echo BASE_URL; ?>turmas/cadastro.php#">Cadastrar Turmas</a>
                    <a href="<?php echo BASE_URL; ?>turmas/consulta.php#">Consultar Turmas</a>
                </div>
            </div>
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownAlunos', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">class</span>
                    <div class="text_sidebar">Alunos <span class="arrow">▼</span></div>
                </a>
                <div id="dropdownAlunos" class="dropdown-content_sidebar">
                    <a href="<?php echo BASE_URL; ?>alunos/cadastro.php#">Cadastrar Aluno</a>
                    <a href="<?php echo BASE_URL; ?>alunos/consulta.php#">Consultar Alunos</a>
                </div>
            </div>
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownUnidades', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">apartment</span>
                    <div class="text_sidebar">Unidade <span class="arrow">▼</span></div>
                </a>
                <div id="dropdownUnidades" class="dropdown-content_sidebar">
                    <a href="<?php echo BASE_URL; ?>unidade/cadastro.php#">Cadastrar Unidade</a>
                    <a href="<?php echo BASE_URL; ?>unidade/consulta.php#">Consultar Unidade</a>
                </div>
            </div>
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownSetor', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">business</span>
                    <div class="text_sidebar">Setor <span class="arrow">▼</span></div>
                </a>
                <div id="dropdownSetor" class="dropdown-content_sidebar">
                    <a href="<?php echo BASE_URL; ?>setor/cadastro.php#">Cadastrar Setor</a>
                    <a href="<?php echo BASE_URL; ?>setor/consulta.php#">Consultar Setor</a>
                </div>
            </div>
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownFuncionarios', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">business_center</span>
                    <div class="text_sidebar">Funcionários <span class="arrow">▼</span></div>
                </a>
                <div id="dropdownFuncionarios" class="dropdown-content_sidebar">
                    <a href="<?php echo BASE_URL; ?>colaboradores/cadastro.php#">Cadastrar Funcionários</a>
                    <a href="<?php echo BASE_URL; ?>colaboradores/consulta.php#">Consultar Funcionários</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($user_role === 'Adm' || $user_role === 'Professor' || $user_role === 'Coordenador') : ?>
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownMotor', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">precision_manufacturing</span>
                    <div class="text_sidebar">Motores<span class="arrow">▼</span></div>
                </a>
                <div id="dropdownMotor" class="dropdown-content_sidebar">
                    <a href="<?php echo BASE_URL; ?>motor/cadastro.php#">Cadastrar Motor</a>
                    <a href="<?php echo BASE_URL; ?>motor/consulta.php#">Consultar Motor</a>
                </div>
            </div>
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownMaquinas', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">settings</span>
                    <div class="text_sidebar">Máquinas<span class="arrow">▼</span></div>
                </a>
                <div id="dropdownMaquinas" class="dropdown-content_sidebar">
                    <a href="<?php echo BASE_URL; ?>maquinas/cadastro.php#">Cadastrar Máquina</a>
                    <a href="<?php echo BASE_URL; ?>maquinas/consulta.php#">Consultar Máquinas</a>
                    <a href="<?php echo BASE_URL; ?>tipo_maquinas/cadastro.php#">Cadastrar Tipo de Máquinas</a>
                    <a href="<?php echo BASE_URL; ?>tipo_maquinas/consulta.php#">Consultar tipo de maquina</a>
                    <a href="<?php echo BASE_URL; ?>tipo_maquinas/tipomaquina_requisito.php#">Relacionar requisitos a maquina</a>
                </div>
            </div>
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownManutencao', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">build</span>
                    <div class="text_sidebar">Manutenção <span class="arrow">▼</span></div>
                </a>
                <div id="dropdownManutencao" class="dropdown-content_sidebar">
                    <a href="<?php echo BASE_URL; ?>manutencao/cadastrar.php#">Cadastrar Manutenção</a>
                    <a href="<?php echo BASE_URL; ?>manutencao/tabela.php#">Consulta de Manutenção</a>
                    <a href="<?php echo BASE_URL; ?>manutencao/maquinas_defeito.php#">Relatório de Manutenção por Aluno</a>
                    <a href="<?php echo BASE_URL; ?>manutencao/consultar_manutencao.php#">Proximas manutenções</a>
                </div>
            </div>
            <a href="<?php echo BASE_URL; ?>historico/consulta.php#" class="Btn_sidebar">
                <span class="material-symbols-outlined">monitoring</span>
                <div class="text_sidebar">Exibir Logs</div>
            </a>
            <a href="<?php echo BASE_URL; ?>documentacao/documentacao.php#" class="Btn_sidebar">
                <span class="material-symbols-outlined">description</span>
                <div class="text_sidebar">Documentação</div>
            </a>
            <div class="dropdown_sidebar">
                <a href="#" class="Btn_sidebar" onclick="toggleDropdown('dropdownErro', this)" aria-expanded="false">
                    <span class="material-symbols-outlined">support_agent</span>
                    <div class="text_sidebar">Suporte<span class="arrow">▼</span></div>
                </a>
                <div id="dropdownErro" class="dropdown-content_sidebar">
                    <a href="<?php echo BASE_URL; ?>erro/cadastrar.php#">Enviar Mensagem</a>
                    <a href="<?php echo BASE_URL; ?>erro/consultar.php#">Consultar Mensagem</a>
                </div>
            </div>
            <a href="<?php echo BASE_URL; ?>Perfil/perfil.php#" class="Btn_sidebar">
                <span class="material-symbols-outlined">person</span>
                <div class="text_sidebar">Perfil</div>
            </a>
        <?php endif; ?>

        <div class="container_botaosair">
            <button class="botaosair" onclick="window.location.href='<?php echo BASE_URL; ?>logout.php'">
                <span class="material-symbols-outlined">logout</span>
                <span>Sair</span>
            </button>
        </div>
    </div>

    <script>
        // (O seu código JavaScript permanece o mesmo)
        function toggleDropdown(dropdownId, element) {
            var dropdownContent = document.getElementById(dropdownId);
            dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
            var isExpanded = element.getAttribute('aria-expanded') === 'true';
            element.setAttribute('aria-expanded', !isExpanded);

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
                menuIcons.forEach(icon => icon.style.display = "none");
                closeIcon.style.display = "inline";
            } else {
                menuIcons.forEach(icon => icon.style.display = "block");
                closeIcon.style.display = "none";
            }
        }
    </script>
</body>

</html>