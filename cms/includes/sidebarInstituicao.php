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
        <li class="menu-escola">
            <a href="carteirinhas.php"><i class="fas fa-id-card"></i> Carteirinhas</a>
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
ajustarMenuInstituicao();
</script>