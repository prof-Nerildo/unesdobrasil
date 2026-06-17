<?php include_once '../includes/headerUnes.php'; ?>
<?php include_once '../includes/sidebarUnes.php'; ?>

<main class="content">
    <section class="main-section">
        <header class="top-bar">
            <h1>Bem-vindo, <span id="nomeUsuario">...</span></h1>
        </header>

        <div class="container-fluid" style="padding: 20px;">
            <div class="row-dashboard">
                <h2 class="sub-titulo-dash"><i class="fas fa-university"></i> Gestão de Instituições</h2>
                <?php include '../componentes/cards_instituicoes.php'; ?>
            </div>

            <hr class="divider-dash">

            <div class="row-dashboard">
                <h2 class="sub-titulo-dash"><i class="fas fa-id-card"></i> Produção Global de Documentos</h2>
                <?php include '../componentes/cards_documentos.php'; ?>
            </div>
        </div>
    </section>

    <?php include_once '../includes/footer.php'; ?>
</main>

<style>
    .sub-titulo-dash { font-size: 13px; font-weight: 700; color: #4a5568; text-transform: uppercase; margin-bottom: 15px; padding-left: 5px; opacity: 0.8; }
    .divider-dash { border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0; }
    /* Garante que o grid de 3 colunas funcione nos dois componentes */
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
</style>

<script>
    // 1. Função de Navegação para a UNES
    function navegarFiltroDoc(status) {
        // Manda para a tela global que vamos criar, passando o status
        window.location.href = `producao-global.php?status=${status}`;
    }

    // 2. Função Principal de Carga
    async function carregarDashboardUnes() {
        try {
            // A. Carrega o Nome do Nerildo
            const resPerfil = await chamarApi('/account/me');
            if(!resPerfil.erro) {
                document.getElementById('nomeUsuario').innerText = resPerfil.dados.nome.split(' ')[0];
            }

            // B. Carrega Resumo das Instituições (Linha 1)
            // Rota: devolve { validar: X, sem_catraca: Y, com_catraca: Z }
            const resInst = await chamarApi('/instituicao/resumo-unes');
            if(!resInst.erro) {
                const i = resInst.dados;
                if(document.getElementById('qtdValidar'))    document.getElementById('qtdValidar').innerText = i.validar || 0;
                if(document.getElementById('qtdSemCatraca')) document.getElementById('qtdSemCatraca').innerText = i.sem_catraca || 0;
                if(document.getElementById('qtdComCatraca')) document.getElementById('qtdComCatraca').innerText = i.com_catraca || 0;
            }

            // C. Carrega Resumo Global de Produção (Linha 2)
            // Rota: devolve { criados: X, solicitados: Y, producao: Z, produzidos: W, entregues: K }
            // C. Carrega Resumo Global de Produção (Linha 2)
            const resDocs = await chamarApi('/documento/resumo-global');
            if(!resDocs.erro) {
                const d = resDocs.dados;
                // Mapeamento que aceita as variações de nomes do banco
                if(document.getElementById('qtdCriado'))     document.getElementById('qtdCriado').innerText     = d.criados || d.criado || 0;
                if(document.getElementById('qtdSolicitado')) document.getElementById('qtdSolicitado').innerText = d.solicitados || d.solicitado || 0;
                if(document.getElementById('qtdProducao'))   document.getElementById('qtdProducao').innerText   = d.producao || 0;
                if(document.getElementById('qtdProduzido'))  document.getElementById('qtdProduzido').innerText  = d.produzidos || d.produzido || 0;
                if(document.getElementById('qtdEntregue'))   document.getElementById('qtdEntregue').innerText   = d.entregues || d.entregue || 0;
            }

        } catch (error) {
            console.error("Erro ao alimentar o dashboard UNES:", error);
        }
    }

    // 3. Dispara a carga
    document.addEventListener('DOMContentLoaded', carregarDashboardUnes);
</script>
<?php include_once '../includes/footerUnes-2.php'; ?>