<?php include_once '../includes/headerUnes.php'; ?>
<?php include_once '../includes/sidebaUnes.php'; ?>

<main class="content">
    <section class="main-section">
        <header class="top-bar">
            <h1>Bem-vindo, <span id="nomeUsuario">...</span></h1>
        </header>

        <div class="container-fluid" style="padding: 20px;">
            <?php include '../componentes/cards_instituicoes.php'; ?>
        </div>

        
    </section>
    <?php include_once '../includes/footer.php'; ?>
</main>

<script>
    async function carregarDashboard() {
        // Carrega o nome
        const resPerfil = await chamarApi('/account/me');
        if(!resPerfil.erro) document.getElementById('nomeUsuario').innerText = resPerfil.dados.nome;

        // Carrega os números dos cards
        await atualizarCards();
    }
    carregarDashboard();
</script>

<?php include_once '../includes/footerUnes-2.php'; ?>