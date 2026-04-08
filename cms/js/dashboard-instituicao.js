/**
 * Busca o resumo de status e atualiza os cards do dashboard
 */
async function carregarEstatisticasDashboard() {
    const user = obterUsuario();
    if (!user || !user.idInstituicao) return;

    try {
        // Chama a rota que criamos no index.php
        const res = await chamarApi(`/documento/resumo-dashboard/${user.idInstituicao}`);

        if (!res.erro) {
            const d = res.dados;

            // Atualiza os elementos HTML pelos IDs que você já criou
            document.getElementById('qtdCriado').innerText     = d.criados || 0;
            document.getElementById('qtdSolicitado').innerText = d.solicitados || 0;
            document.getElementById('qtdProducao').innerText   = d.producao || 0;
            document.getElementById('qtdProduzido').innerText  = d.produzidos || 0;
            document.getElementById('qtdEntregue').innerText   = d.entregues || 0;
        } else {
            console.error("Erro da API:", res.message);
        }async function carregarEstatisticasDashboard() {
    const user = obterUsuario();
    if (!user || !user.idInstituicao) return;

    try {
        const res = await chamarApi(`/documento/resumo-dashboard/${user.idInstituicao}`);

        if (!res.erro) {
            const d = res.dados;

            // Usamos || para garantir que se um nome falhar, ele tenta o outro ou mostra 0
            document.getElementById('qtdCriado').innerText     = d.criados || d.criado || 0;
            document.getElementById('qtdSolicitado').innerText = d.solicitados || d.solicitado || 0;
            document.getElementById('qtdProducao').innerText   = d.producao || 0;
            document.getElementById('qtdProduzido').innerText  = d.produzidos || d.produzido || 0;
            document.getElementById('qtdEntregue').innerText   = d.entregues || d.entregue || 0;
            
        } else {
            console.error("Erro da API:", res.message);
        }
    } catch (error) {
        console.error("Erro ao carregar estatísticas:", error);
    }
}

document.addEventListener('DOMContentLoaded', carregarEstatisticasDashboard);
    } catch (error) {
        console.error("Erro ao carregar estatísticas:", error);
    }
}

// Inicia a carga quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', carregarEstatisticasDashboard);