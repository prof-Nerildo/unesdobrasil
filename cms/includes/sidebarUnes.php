<nav class="sidebar">
    <div class="logo">[ UNES ]</div>
    
    <ul id="menu-navegacao">
        <li><a href="dashboard.php"><i class="fas fa-home"></i> Painel</a></li>
        
        <li><hr style="opacity: 0.1; margin: 10px 0;"></li>

        <li class="menu-unes" style="display:none;">
            <a href="instituicoes.php"><i class="fas fa-university"></i> Instituições</a>
        </li>

        <li class="menu-unes" style="display:none;">
            <a href="javascript:void(0)" onclick="toggleSubmenu(this)" style="display: flex; align-items: center; justify-content: space-between;">
                <span><i class="fas fa-print"></i> Para Impressão</span>
                <i class="fas fa-chevron-right arrow-icon" style="transition: 0.3s; font-size: 0.8rem;"></i>
            </a>
            <ul class="submenu" style="display:none; list-style: none; padding-left: 25px; background: rgba(0,0,0,0.05);">
                <li><a href="producao-global.php" style="padding: 10px 0; display: block;"><i class="fas fa-industry"></i> Documentos</a></li>
                <!--<li><a href="gerar-lotes.php" style="padding: 10px 0; display: block;"><i class="fas fa-file-archive"></i> Gerar ZIP</a></li>-->
            </ul>
        </li>

        <li class="menu-escola" style="display:none;">
            <a href="usuarios.php"><i class="fas fa-users-cog"></i> Usuários</a>
        </li>

        <li><hr style="opacity: 0.1; margin: 10px 0;"></li>

        <li><a href="javascript:void(0)" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
    </ul>
</nav>

<script>
// Função para abrir/fechar submenus
function toggleSubmenu(element) {
    const parent = element.parentElement;
    const submenu = parent.querySelector('.submenu');
    const arrow = parent.querySelector('.arrow-icon');
    
    if (submenu.style.display === 'none' || submenu.style.display === '') {
        submenu.style.display = 'block';
        if(arrow) arrow.style.transform = 'rotate(90deg)';
    } else {
        submenu.style.display = 'none';
        if(arrow) arrow.style.transform = 'rotate(0deg)';
    }
}

function ajustarMenuPorAcl() {
    const raw = localStorage.getItem('user_unes') || localStorage.getItem('user');
    let nivel = "";

    if (raw) {
        const user = JSON.parse(raw);
        // Tenta pegar de qualquer lugar
        nivel = (user.nivel_acesso || user.idAcl || user.nivel || "").toString().toLowerCase().trim();
    }

    console.log("NÍVEL ENCONTRADO:", nivel);

    // SE O NÍVEL VIER VAZIO (seu caso atual), vamos liberar por padrão 
    // para você não ficar travado no desenvolvimento
    if (nivel === "unes" || nivel === "master" || nivel === "" || nivel === "1") {
        const itens = document.querySelectorAll('.menu-unes');
        itens.forEach(item => {
            item.style.setProperty('display', 'block', 'important');
        });
        console.warn("AVISO: Menu liberado por contingência (nível vazio).");
    }
}

// Executa assim que a página carregar e dá um pequeno delay de 100ms por segurança
window.addEventListener('load', () => {
    setTimeout(ajustarMenuPorAcl, 100);
});
</script>