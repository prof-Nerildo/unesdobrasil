<nav class="sidebar inst-bg">
    <div class="logo">[ UNES - ESCOLA ]</div>
    <ul id="menu-navegacao">
        <li><a href="dashboard.php"><i class="fas fa-home"></i> Início</a></li>
        <li><hr style="opacity: 0.1; margin: 10px 0;"></li>
        <!--li class="menu-escola">
            <a href="usuarios.php"><i class="fas fa-users-cog"></i> Usuários</a>
        </li>
        <li><hr style="opacity: 0.1; margin: 10px 0;"></li-->
        <li class="menu-escola">
            <a href="instituicao.php"><i class="fas fa-university"></i> Minha Instituição</a>
        </li>
        <li><hr style="opacity: 0.1; margin: 10px 0;"></li>
        <li class="menu-item">
            <div class="menu-dropdown-header" onclick="toggleSubmenu('submenu-carteirinhas')">
                <span><i class="fas fa-id-card"></i> Carteirinhas</span>
                <i class="fas fa-chevron-down arrow-icon"></i>
            </div>
            <ul id="submenu-carteirinhas" class="submenu">
                <li>
                    <a href="nova-solicitacao.php"><i class="fas fa-plus"></i> Nova Solicitação</a>
                </li>
                <li>
                    <a href="em-producao.php"><i class="fas fa-hammer"></i> Em Produção</a>
                </li>
                <li>
                    <a href="entregues.php"><i class="fas fa-check-double"></i> Entregues</a>
                </li>
            </ul>
        </li>

        
        <li><hr style="opacity: 0.1; margin: 10px 0;"></li>
        <li><a href="javascript:void(0)" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
    </ul>
</nav>

<script>
function ajustarMenuInstituicao() {
    const dados = localStorage.getItem('user_unes');
    if (dados) {
        const user = JSON.parse(dados);
        // Lembre-se: No seu login.html, res.usuario.id agora é o idAcl
        const acl = parseInt(user.id); 

        console.log("DEBUG INSTITUIÇÃO - Seu nível ACL é:", acl);

        // Se for Instituição (3) ou Master (1)
        if (acl === 3 || acl === 1) {
            const itens = document.getElementsByClassName('menu-escola');
            for (let i = 0; i < itens.length; i++) {
                itens[i].style.display = 'block';
            }
        }
    }
}

function toggleSubmenu(id) {
    const submenu = document.getElementById(id);
    const menuItem = submenu.parentElement;
    
    // Alterna a classe active no submenu
    submenu.classList.toggle('active');
    
    // Alterna a classe open no li pai (para girar a seta via CSS)
    menuItem.classList.toggle('open');
}
ajustarMenuInstituicao();
</script>