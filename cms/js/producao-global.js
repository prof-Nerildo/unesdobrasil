/**
 * producao-global.js
 * Gerenciamento da esteira de produção UNES (Layout Tabela com Paginação de 10)
 */

let todosDocumentosCache = []; // Guarda os dados para paginar sem ir ao banco toda hora
let paginaAtual = 1;
const itensPorPagina = 10;

async function carregarDadosDashboard() {
    try {
        const res = await chamarApi('/documento/resumo-global');
        if(!res.erro) {
            const d = res.dados;
            document.getElementById('qtdCriado').innerText     = d.criados || 0;
            document.getElementById('qtdSolicitado').innerText = d.solicitados || 0;
            document.getElementById('qtdProducao').innerText   = d.producao || 0;
            document.getElementById('qtdProduzido').innerText  = d.produzidos || 0;
            document.getElementById('qtdEntregue').innerText   = d.entregues || 0;
        }
    } catch (e) { console.error("Erro dashboard:", e); }
}

async function carregarProducao() {
    const status = document.getElementById('filtroStatus').value;
    const corpo = document.getElementById('tabelaProducaoCorpo');
    corpo.innerHTML = '<tr><td colspan="10" class="text-center" style="padding: 50px; color: #a0aec0;">Carregando dados da esteira...</td></tr>';

    try {
        const res = await chamarApi(`/documento/producao-global/${status}`);
        
        if(!res.erro && res.dados) {
            todosDocumentosCache = res.dados;
            paginaAtual = 1; // Sempre volta para a primeira página ao trocar filtro
            renderizarTabela();
        } else {
            todosDocumentosCache = [];
            corpo.innerHTML = '<tr><td colspan="10" class="text-center" style="padding: 50px;">Nenhum registro encontrado.</td></tr>';
            renderizarPaginador(0);
        }
    } catch (e) { console.error("Erro carga produção:", e); }
}

function renderizarTabela() {
    const corpo = document.getElementById('tabelaProducaoCorpo');
    const busca = document.getElementById('buscaGlobal').value.toLowerCase();
    
    // 1. Filtra os documentos baseados na busca
    const filtrados = todosDocumentosCache.filter(doc => {
        const stringBusca = `${doc.InsEnsinoDocumento} ${doc.NomeDocumento} ${doc.nCPF} ${doc.idCard}`.toLowerCase();
        return stringBusca.includes(busca);
    });

    // 2. Lógica de Paginação (Corte de 10 em 10)
    const inicio = (paginaAtual - 1) * itensPorPagina;
    const fim = inicio + itensPorPagina;
    const listaPaginada = filtrados.slice(inicio, fim);

    // 3. Monta o HTML
    let html = "";
    if (listaPaginada.length > 0) {
        listaPaginada.forEach(doc => {
            // Formata as datas para o padrão BR
            const dCriacao = doc.dataCriacao ? doc.dataCriacao.split(' ')[0].split('-').reverse().join('/') : '--/--/----';
            const dNasc = doc.dataNascDocumento ? doc.dataNascDocumento.split('-').reverse().join('/') : '--/--/----';

            html += `
                <tr class="linha-aluno">
                    <td class="text-bold" style="color:#3182ce;">
                        ${doc.idCard}
                    </td>
                    <td class="text-bold">${doc.NomeDocumento.toUpperCase()}</td>
                    <td style="font-size: 11px; color: #718096;">${doc.InsEnsinoDocumento}</td>
                    <td>${doc.serieDocumento}</td>
                    <td>${doc.nCPF}</td>
                    <td>${doc.nRGDocumento || '--'}</td> 
                    <td>${dNasc}</td> 
                    <td class="text-center">
                        <img src="../../${doc.fotoDocumento}" class="img-table-thumb">
                    </td>
                    <td class="text-center text-bold" style="color: #4a5568;">
                        ${dCriacao}
                    </td>
                </tr>`;
        });
    } else {
        html = '<tr><td colspan="10" class="text-center" style="padding: 30px;">Nenhum registro encontrado para a busca.</td></tr>';
    }

    corpo.innerHTML = html;
    renderizarPaginador(filtrados.length);
}

function renderizarPaginador(totalItens) {
    let container = document.getElementById('paginador-v2');
    
    if (!container) {
        const div = document.createElement('div');
        div.id = 'paginador-v2';
        div.style = "display:flex; justify-content:center; gap:5px; padding:15px; background:#fff; border-top:1px solid #eee;";
        // Tentativa de anexar após a tabela se o card existir
        const cardRef = document.querySelector('.card');
        if(cardRef) cardRef.appendChild(div);
        container = div;
    }

    const totalPaginas = Math.ceil(totalItens / itensPorPagina);
    let htmlPaginacao = "";

    if (totalPaginas > 1) {
        for (let i = 1; i <= totalPaginas; i++) {
            const style = i === paginaAtual 
                ? "background:#3182ce; color:#fff; border:1px solid #3182ce;" 
                : "background:#fff; color:#333; border:1px solid #ddd;";
            
            htmlPaginacao += `<button onclick="mudarPagina(${i})" style="${style} padding:5px 12px; cursor:pointer; border-radius:4px; font-weight:bold;">${i}</button>`;
        }
    }
    container.innerHTML = htmlPaginacao;
}

function mudarPagina(num) {
    paginaAtual = num;
    renderizarTabela();
    window.scrollTo({ top: 300, behavior: 'smooth' });
}

function setFilter(statusId) {
    document.getElementById('filtroStatus').value = statusId;
    
    // UI Feedback nos cards
    document.querySelectorAll('.filter-card').forEach(c => c.classList.remove('active'));
    const cardAtivo = document.querySelector(`.filter-card[data-status="${statusId}"]`);
    if(cardAtivo) cardAtivo.classList.add('active');

    // Referência dos Botões
    const btnMover = document.getElementById('btnMoverProducao');
    const btnZip = document.querySelector('button[onclick="gerarLoteSelecionado()"]');
    const btnEntregar = document.getElementById('btnFinalizarEntrega');

    // Reseta visibilidade (Esconde todos)
    if(btnMover) btnMover.style.display = 'none';
    if(btnZip) btnZip.style.display = 'none';
    if(btnEntregar) btnEntregar.style.display = 'none';

    // Lógica Contextual
    if(statusId == 5) { 
        // Aba SOLICITADOS
        if(btnMover) btnMover.style.display = 'block';
    } 
    else if(statusId == 6) { 
        // Aba EM PRODUÇÃO
        if(btnZip) btnZip.style.display = 'block';
    } 
    else if(statusId == 7) { 
        // Aba PRODUZIDOS
        if(btnEntregar) btnEntregar.style.display = 'block';
    }

    const labels = {9: 'CRIADOS', 5: 'SOLICITADOS', 6: 'EM PRODUÇÃO', 7: 'PRODUZIDOS', 8: 'ENTREGUES'};
    document.getElementById('tituloStatus').innerHTML = `<i class="fas fa-list"></i> EXIBINDO: ${labels[statusId]}`;
    
    carregarProducao();
}

// NOVA FUNÇÃO: Move de Produzido (7) para Entregue (8)
async function marcarComoEntregueLote() {
    const listaParaEntregar = todosDocumentosCache;

    if (listaParaEntregar.length === 0) {
        alert("⚠️ Não há documentos produzidos para marcar como entregue.");
        return;
    }

    const confirmacao = confirm(`🚚 Deseja marcar TODOS os ${listaParaEntregar.length} documentos como ENTREGUES?`);
    if (!confirmacao) return;

    const btn = document.getElementById('btnFinalizarEntrega');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> PROCESSANDO...';

    try {
        // Mapeia os IDs e atualiza para Status 8
        const promessas = listaParaEntregar.map(doc => 
            chamarApi(`/documento/status/${doc.idCard}`, 'PUT', { novoStatus: 8 })
        );

        await Promise.all(promessas);
        
        alert("✅ Sucesso! Os documentos foram marcados como entregues e arquivados.");
        
        carregarDadosDashboard();
        carregarProducao();

    } catch (e) {
        console.error(e);
        alert("🚫 Erro ao processar a entrega do lote.");
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-double"></i> MARCAR COMO ENTREGUE';
    }
}


function filtrarTabela() {
    paginaAtual = 1;
    renderizarTabela();
}

async function moverParaProducaoLote() {
    const listaParaMover = todosDocumentosCache;

    if (listaParaMover.length === 0) {
        alert("⚠️ Não há documentos nesta lista para mover.");
        return;
    }

    const confirmacao = confirm(`🚀 ATENÇÃO: Você está prestes a mover TODOS os ${listaParaMover.length} documentos desta lista para "Em Produção".\n\nConfirma esta operação?`);
    
    if (!confirmacao) return;

    const btn = document.getElementById('btnMoverProducao');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> MOVENDO TUDO...';

    try {
        const promessas = listaParaMover.map(doc => 
            chamarApi(`/documento/status/${doc.idCard}`, 'PUT', { novoStatus: 6 })
        );

        await Promise.all(promessas);
        alert(`✅ Sucesso! ${listaParaMover.length} documentos foram movidos para a esteira de Produção.`);
        
        carregarDadosDashboard();
        carregarProducao();
    } catch (e) {
        console.error(e);
        alert("🚫 Erro ao tentar mover o lote completo.");
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-tools"></i> MOVER P/ PRODUÇÃO';
    }
}

async function gerarLoteSelecionado() {
    // 1. Pega TODOS os documentos da lista atual (sem precisar marcar nada)
    const listaParaLote = todosDocumentosCache;

    if (listaParaLote.length === 0) {
        alert("⚠️ Não há documentos nesta lista para gerar lote.");
        return;
    }

    const confirmacao = confirm(`📦 Deseja gerar o lote ZIP para os ${listaParaLote.length} documentos em produção?`);
    if (!confirmacao) return;

    const btnZip = document.querySelector('button[onclick="gerarLoteSelecionado()"]');
    btnZip.disabled = true;
    btnZip.innerHTML = '<i class="fas fa-spinner fa-spin"></i> GERANDO LOTE...';

    try {
        // Mapeia todos os IDs do lote carregado
        const idsParaProcessar = listaParaLote.map(doc => doc.idCard);

        const res = await chamarApi('/documento/gerar-lote', 'POST', { ids: idsParaProcessar });

        if (!res.erro) {
           // Substitua a linha do window.location por esta:
            window.location.href = `../../saas/Dependencies/download.php?file=${res.file}`;
            
            alert("✅ Lote gerado com sucesso! Os documentos foram movidos para 'Produzidos'.");
            carregarDadosDashboard();
            carregarProducao();
        } else {
            alert("🚫 Erro: " + res.message);
        }
    } catch (e) {
        alert("🚫 Erro ao processar o lote.");
    } finally {
        btnZip.disabled = false;
        btnZip.innerHTML = '<i class="fas fa-file-archive"></i> GERAR LOTE (ZIP)';
    }
}



// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    carregarDadosDashboard();
    setFilter(5); 
});