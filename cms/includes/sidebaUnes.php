<nav class="sidebar">
    <div class="logo">[ UNES ]</div>
    <ul id="menu-navegacao">

        <li> <a href="dashboard.php"><i class="fas fa-home"></i> Início</a></li>
        <li><hr style="opacity: 0.1; margin: 10px 0;"></li>

        <li class="menu-escola" style="display:none;">
            <a href="usuarios.php"><i class="fas fa-users-cog"></i> Usuários</a>
        </li>
  
        <li><hr style="opacity: 0.1; margin: 10px 0;"></li>
        <li class="menu-unes">
            <a href="instituicoes.php"><i class="fas fa-university"></i> Instituições</a>
        </li>
        

        <li><hr style="opacity: 0.1; margin: 10px 0;"></li>
        

        <li> <a href="javascript:void(0)" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Sair</a></li>

    </ul>
</nav>

<script>
function ajustarMenuPorAcl() {
    // 1. Pega os dados que o seu login salvou
    const dados = localStorage.getItem('user_unes');
    
    if (dados) {
        const user = JSON.parse(dados);
        
        // 2. Lógica baseada no seu JSON (nivel_acesso: "Unes")
        const nivel = user.nivel_acesso; 

        console.log("DEBUG SIDEBAR - Seu nível é:", nivel);

        // Se for Master ou Unes, mostramos o menu de Instituições
        if (nivel === "Unes" || nivel === "Master") {
            const itens = document.getElementsByClassName('menu-unes');
            for (let i = 0; i < itens.length; i++) {
                itens[i].style.display = 'block';
            }
        }
        
        // Se no futuro tivermos "Instituicao", você adiciona aqui:
        if (nivel === "Instituicao") {
            // mostrar menus da escola...
        }

    } else {
        console.error("DEBUG SIDEBAR - Usuário não logado!");
    }
}

// Executa a função
ajustarMenuPorAcl();
</script>