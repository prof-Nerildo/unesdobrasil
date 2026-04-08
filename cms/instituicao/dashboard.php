<?php include_once '../includes/headerInstituicao.php'; ?>
<?php include_once '../includes/sidebarInstituicao.php'; ?>

<main class="content">
    <section class="main-section">
        <header class="top-bar">
            <h1>Bem-vindo, <span id="nomeUsuario">...</span></h1>
        </header>

        <div class="container-fluid" style="padding: 20px;">
            <h2 style="font-size: 1.5rem; color: var(--primary); font-weight: 600;">
                Resumo de Carteirinhas
            </h2>
            
            <?php include '../componentes/cards_documentos.php'; ?>
        </div>

    </section>

    <?php include_once '../includes/footer.php'; ?>
</main>

<script>
    async function carregarDashboardInstituicao() {
        try {
            // 1. Carrega os dados do perfil logado (Adriano, Nerildo, etc)
            const resPerfil = await chamarApi('/account/me');
            if(!resPerfil.erro) {
                document.getElementById('nomeUsuario').innerText = resPerfil.dados.nome;
                // Se o seu retorno do /me tiver o nome da escola, você preenche aqui:
                if(resPerfil.dados.nome_instituicao) {
                    document.getElementById('nomeEscola').innerText = resPerfil.dados.nome_instituicao;
                }
            }

            // 2. Carrega os números dos cards (Criado, Solicitado, etc)
            // Esta função deve estar dentro do seu componente ou no seu api.js
            if (typeof atualizarCardsDocumentos === 'function') {
                await atualizarCardsDocumentos();
            }
            
        } catch (error) {
            console.error("Erro ao carregar dashboard:", error);
        }
    }

    // Dispara a carga
    carregarDashboardInstituicao();
</script>

<script src="../js/dashboard-instituicao.js"></script>
<?php include_once '../includes/footerUnes-2.php'; ?>