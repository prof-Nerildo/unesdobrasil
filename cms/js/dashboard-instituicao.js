/**
 * dashboard-instituicao.js
 * Carrega os totais por status nos cards e navega para as páginas corretas.
 */

/**
 * Busca o resumo de status e atualiza os cards do dashboard
 */
async function carregarEstatisticasDashboard() {
    const user = obterUsuario();
    if (!user || !user.idInstituicao) return;

    try {
        const res = await chamarApi(`/documento/resumo-dashboard/${user.idInstituicao}`);

        if (!res.erro) {
            const d = res.dados;

            const set = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.innerText = val || 0;
            };

            set('qtdCriado',    d.criados    || d.criado    || 0);
            set('qtdSolicitado',d.solicitados || d.solicitado || 0);
            set('qtdProducao',  d.producao   || 0);
            set('qtdProduzido', d.produzidos || d.produzido  || 0);
            set('qtdEntregue',  d.entregues  || d.entregue   || 0);

        } else {
            console.error("Erro da API:", res.message);
        }
    } catch (error) {
        console.error("Erro ao carregar estatísticas:", error);
    }
}

/**
 * Redireciona para a página correta com base no status clicado.
 * Para "entregue" vai para entregues.php.
 * Para os demais vai para em-producao.php?status=<idStatus>
 */
function navegarFiltroDoc(status) {
    const mapaStatus = {
        'criado':     9,
        'solicitado': 5,
        'producao':   6,
        'produzido':  7
    };

    if (status === 'entregue') {
        window.location.href = 'entregues.php';
    } else {
        const idStatus = mapaStatus[status] || 5;
        window.location.href = `em-producao.php?status=${idStatus}`;
    }
}

// Inicia a carga quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', carregarEstatisticasDashboard);