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

/**
 * Redireciona para a página correta com base no status clicado
 * @param {string} status - O nome do status vindo do card
 */
function navegarFiltroDoc(status) {
    // Se clicar no card de Entregues, vai para a página de histórico final
    if (status === 'entregue') {
        window.location.href = 'entregues.php';
    } 
    // Para todos os outros status (Criado, Solicitado, Produção, Produzido), 
    // vai para a tela de gestão de produção
    else {
        window.location.href = 'em-producao.php';
    }
}

// Inicia a carga quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', carregarEstatisticasDashboard);