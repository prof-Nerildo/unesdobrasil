<?php include_once 'includes/header.php'; ?>
<?php include_once 'includes/sidebar.php'; ?>

<main class="content">
    <section class="main-section">
        <header class="top-bar">
            <h1>Bem-vindo, <span id="nomeUsuario">...</span></h1>
        </header>

        <section class="stats">
            <div class="card">
                <h3>Instituições Ativas</h3>
                <p id="totalInstituicoes">0</p>
            </div>
        </section>

        
    </section>
    <?php include_once 'includes/footer.php'; ?>
</main>

<script>
    async function carregarDashboard() {
        try {
            const resPerfil = await chamarApi('/account/me');
            if(!resPerfil.erro) document.getElementById('nomeUsuario').innerText = resPerfil.dados.nome;

            const resInst = await chamarApi('/instituicao/todas');
            const lista = Array.isArray(resInst) ? resInst : (resInst.dados || []);
            document.getElementById('totalInstituicoes').innerText = lista.length;
        } catch (error) {
            console.error("Erro dashboard:", error);
        }
    }
    carregarDashboard();
</script>

<?php include_once 'includes/footer2.php'; ?>