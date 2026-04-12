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


<style>
.stats-grid {
    display: grid;
    /* Define 3 colunas de larguras iguais */
    grid-template-columns: repeat(3, 1fr); 
    gap: 20px;
    padding: 20px 0;
}

/* Ajuste para telas menores (Responsividade) */
@media (max-width: 992px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr); /* 2 por linha em tablets */
    }
}

@media (max-width: 600px) {
    .stats-grid {
        grid-template-columns: 1fr; /* 1 por linha no celular */
    }
}

.card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    transition: transform 0.2s;
    min-height: 100px;
}

.card:hover {
    transform: translateY(-5px);
}

.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    background: #f8fafc; /* Fundo suave para os ícones */
}

.card-info h3 {
    margin: 0;
    font-size: 1.5rem;
    color: #2d3748;
}

.card-info p {
    margin: 0;
    font-size: 0.8rem;
    font-weight: 700;
    color: #718096;
    text-transform: uppercase;
}
</style>

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