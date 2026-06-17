/**
 * producao-global.js - VERSÃO COMPLETA RECUPERADA
 */
let todosDocumentosCache = [];
let paginaAtual = 1;
const itensPorPagina = 10;
let ordemAscendente = true;
let ultimaColunaSorteada = '';

// 1. Formatação de Data e Hora
function formatarDataHoraBR(dataString) {
    if (!dataString || dataString === '0000-00-00 00:00:00') return '--/--/---- --:--:--';
    const data = new Date(dataString);
    const dia = String(data.getDate()).padStart(2, '0');
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const ano = data.getFullYear();
    const hora = String(data.getHours()).padStart(2, '0');
    const minuto = String(data.getMinutes()).padStart(2, '0');
    const seg = String(data.getSeconds()).padStart(2, '0');
    // Sem <br>: usa flex-column via CSS para separação visual.
    // Ao copiar para o Excel, data e hora ficam na mesma célula/linha.
    return `<span class="celula-data"><span class="data-dt">${dia}/${mes}/${ano}</span><span class="hora-dt"> ${hora}:${minuto}:${seg}</span></span>`;
}

// 2. Carga do Dashboard (Cards do Topo)
async function carregarDadosDashboard() {
    try {
        const res = await chamarApi('/documento/resumo-global');
        if (!res.erro) {
            const d = res.dados;
            if (document.getElementById('qtdCriado')) document.getElementById('qtdCriado').innerText = d.criados || 0;
            if (document.getElementById('qtdSolicitado')) document.getElementById('qtdSolicitado').innerText = d.solicitados || 0;
            if (document.getElementById('qtdProducao')) document.getElementById('qtdProducao').innerText = d.producao || 0;
            if (document.getElementById('qtdProduzido')) document.getElementById('qtdProduzido').innerText = d.produzidos || 0;
            if (document.getElementById('qtdEntregue')) document.getElementById('qtdEntregue').innerText = d.entregues || 0;
        }
    } catch (e) { console.error("Erro dashboard:", e); }
}

// 3. Busca os dados no Banco
async function carregarProducao() {
    const status = document.getElementById('filtroStatus').value;
    const corpo = document.getElementById('tabelaProducaoCorpo');
    corpo.innerHTML = '<tr><td colspan="11" class="text-center" style="padding: 50px; color: #a0aec0;"><i class="fas fa-spinner fa-spin"></i> Carregando esteira...</td></tr>';

    try {
        const res = await chamarApi(`/documento/producao-global/${status}`);
        if (!res.erro && res.dados) {
            todosDocumentosCache = res.dados;
            paginaAtual = 1;
            renderizarTabela();
        } else {
            todosDocumentosCache = [];
            corpo.innerHTML = '<tr><td colspan="11" class="text-center" style="padding: 50px;">Nenhum registro encontrado.</td></tr>';
            renderizarPaginador(0);
        }
    } catch (e) { console.error("Erro carga produção:", e); }
}

// 4. Ordenação das Colunas
function ordenarEsteira(coluna) {
    if (ultimaColunaSorteada === coluna) {
        ordemAscendente = !ordemAscendente;
    } else {
        ordemAscendente = true;
        ultimaColunaSorteada = coluna;
    }

    todosDocumentosCache.sort((a, b) => {
        let valA = a[coluna] || '';
        let valB = b[coluna] || '';

        // 1. Lógica para Números (IDCARD)
        if (coluna === 'idCard') {
            return ordemAscendente ? (parseInt(valA) - parseInt(valB)) : (parseInt(valB) - parseInt(valA));
        }

        // 2. Lógica para Strings (Nomes, Instituição, etc)
        valA = valA.toString().toLowerCase();
        valB = valB.toString().toLowerCase();

        if (valA < valB) return ordemAscendente ? -1 : 1;
        if (valA > valB) return ordemAscendente ? 1 : -1;
        return 0;
    });

    paginaAtual = 1; // Sempre volta para a primeira página ao ordenar
    renderizarTabela();
}

// 5. Renderização da Tabela (O Coração do Script)
function renderizarTabela() {
    const corpo = document.getElementById('tabelaProducaoCorpo');
    const busca = document.getElementById('buscaGlobal').value.toLowerCase().trim();

    // Atualiza ícones das setinhas
    document.querySelectorAll('th i.fas').forEach(icon => {
        const col = icon.id.replace('sort-', '');
        if (col === ultimaColunaSorteada) {
            icon.className = ordemAscendente ? 'fas fa-sort-up' : 'fas fa-sort-down';
            icon.style.opacity = '1';
        } else {
            icon.className = 'fas fa-sort';
            icon.style.opacity = '0.3';
        }
    });

    // Filtro em todos os campos
    const filtrados = todosDocumentosCache.filter(doc => {
        return `${doc.idCard} ${doc.NomeDocumento} ${doc.InsEnsinoDocumento} ${doc.nCPF} ${doc.nRGDocumento}`.toLowerCase().includes(busca);
    });

    const inicio = (paginaAtual - 1) * itensPorPagina;
    const listaPaginada = filtrados.slice(inicio, inicio + itensPorPagina);

    let html = "";
    if (listaPaginada.length > 0) {
        const statusAtual = parseInt(document.getElementById('filtroStatus').value);
        listaPaginada.forEach(doc => {
            const dSolicitacao = formatarDataHoraBR(doc.dataCriacao);
            // Substitua a linha da dAlteracao por esta:
            const dAlteracao = formatarDataHoraBR(doc.dataAlteracao || doc.updated_at || doc.data_atualizacao);
            const dNasc = doc.dataNascDocumento ? doc.dataNascDocumento.split('-').reverse().join('/') : '--/--/----';

            // Botões individuais: Voltar (status anterior) + Avançar (status seguinte)
            let btnVoltar  = '';
            let btnAvancar = '';

            if (statusAtual === 5) {
                // Solicitado → só avança para Em Produção
                btnAvancar = `<button title="Mover p/ Em Produção" onclick="alterarStatusIndividual('${doc.idCard}', 6, 'Mover para Em Produção?')" class="btn-mini-acao" style="background:#4e54c8;"><i class="fas fa-tools"></i></button>`;
            } else if (statusAtual === 6) {
                // Em Produção → volta p/ Solicitado | avança p/ Produzido
                btnVoltar  = `<button title="Voltar p/ Solicitados" onclick="alterarStatusIndividual('${doc.idCard}', 5, 'Voltar para Solicitados?')" class="btn-mini-acao" style="background:#ffbc00;"><i class="fas fa-arrow-left"></i></button>`;
                btnAvancar = `<button title="Avançar p/ Produzidos" onclick="alterarStatusIndividual('${doc.idCard}', 7, 'Marcar como Produzido?')" class="btn-mini-acao" style="background:#27ae60;"><i class="fas fa-id-card"></i></button>`;
            } else if (statusAtual === 7) {
                // Produzido → volta p/ Em Produção | avança p/ Entregue
                btnVoltar  = `<button title="Voltar p/ Em Produção" onclick="alterarStatusIndividual('${doc.idCard}', 6, 'Voltar para Em Produção?')" class="btn-mini-acao" style="background:#4e54c8;"><i class="fas fa-arrow-left"></i></button>`;
                btnAvancar = `<button title="Confirmar Entrega" onclick="alterarStatusIndividual('${doc.idCard}', 8, 'Confirmar Entrega?')" class="btn-mini-acao" style="background:#2c3e50;"><i class="fas fa-truck"></i></button>`;
            } else if (statusAtual === 8) {
                // Entregue → só volta p/ Produzido
                btnVoltar  = `<button title="Voltar p/ Produzidos" onclick="alterarStatusIndividual('${doc.idCard}', 7, 'Voltar para Produzidos?')" class="btn-mini-acao" style="background:#27ae60;"><i class="fas fa-arrow-left"></i></button>`;
            }

            const btnAcao = `<div class="acoes-linha">${btnVoltar}${btnAvancar}</div>`;

            html += `
                <tr class="linha-aluno">
                    <td class="text-bold" style="color:#3182ce; white-space:nowrap;">${doc.idCard}</td>
                    <td class="text-bold">${doc.NomeDocumento.toUpperCase()}</td>
                    <td style="font-size: 11px; color:#718096;">${doc.InsEnsinoDocumento}</td>
                    <td>${doc.serieDocumento}</td>
                    <td>${doc.nCPF}</td>
                    <td>${doc.nRGDocumento || '--'}</td> 
                    <td>${dNasc}</td> 
                    <td class="text-center"><img src="../../${doc.fotoDocumento}" class="img-table-thumb"></td>
                    <td class="text-center text-bold" style="font-size:12px;">${dSolicitacao}</td>
                    <td class="text-center text-bold" style="font-size:12px;">${dAlteracao}</td>
                    <td class="text-center">${btnAcao}</td>
                </tr>`;
        });
    } else {
        html = '<tr><td colspan="11" class="text-center" style="padding:30px;">Nenhum registro encontrado.</td></tr>';
    }
    corpo.innerHTML = html;
    renderizarPaginador(filtrados.length);
}

// 6. Paginação (padrão instituicoes.php)
function renderizarPaginador(totalItens) {
    const container = document.getElementById('paginador-v2');
    if (!container) return;
    container.innerHTML = "";

    const totalPaginas = Math.ceil(totalItens / itensPorPagina);
    if (totalPaginas <= 1) return;

    let maxBotoes = 5;
    let inicio = Math.max(1, paginaAtual - Math.floor(maxBotoes / 2));
    let fim = Math.min(totalPaginas, inicio + maxBotoes - 1);

    // Botões "PRIMEIRA" e "ANTERIOR"
    if (paginaAtual > 1) {
        container.appendChild(criarBotaoPag(1, '<i class="fas fa-angle-double-left"></i>', false));
        container.appendChild(criarBotaoPag(paginaAtual - 1, '<i class="fas fa-angle-left"></i>', false));
    }

    // Números
    for (let i = inicio; i <= fim; i++) {
        container.appendChild(criarBotaoPag(i, i, i === paginaAtual));
    }

    // Botões "PRÓXIMA" e "ÚLTIMA"
    if (paginaAtual < totalPaginas) {
        container.appendChild(criarBotaoPag(paginaAtual + 1, '<i class="fas fa-angle-right"></i>', false));
        container.appendChild(criarBotaoPag(totalPaginas, '<i class="fas fa-angle-double-right"></i>', false));
    }
}

function criarBotaoPag(pagina, html, ativo) {
    const btn = document.createElement('button');
    btn.innerHTML = html;
    btn.className = `btn-pag ${ativo ? 'active' : ''}`;
    btn.onclick = () => {
        paginaAtual = pagina;
        renderizarTabela();
        window.scrollTo({ top: 300, behavior: 'smooth' });
    };
    return btn;
}

function mudarPagina(num) { paginaAtual = num; renderizarTabela(); window.scrollTo({ top: 300, behavior: 'smooth' }); }
function filtrarTabela() { paginaAtual = 1; renderizarTabela(); }

// 7. Ações Individuais e em Lote
async function alterarStatusIndividual(idCard, novoStatus, mensagem) {
    if (!confirm(mensagem)) return;
    try {
        const res = await chamarApi(`/documento/status/${idCard}`, 'PUT', { novoStatus: novoStatus });
        if (!res.erro) {
            todosDocumentosCache = todosDocumentosCache.filter(doc => doc.idCard !== idCard);
            renderizarTabela();
            carregarDadosDashboard();
        }
    } catch (e) { console.error(e); }
}

async function moverParaProducaoLote() {
    if (todosDocumentosCache.length === 0) return;
    if (!confirm(`🚀 Mover TODOS para Produção?`)) return;
    try {
        await Promise.all(todosDocumentosCache.map(doc => chamarApi(`/documento/status/${doc.idCard}`, 'PUT', { novoStatus: 6 })));
        carregarDadosDashboard(); carregarProducao();
    } catch (e) { console.error(e); }
}

async function gerarLoteSelecionado() {
    if (todosDocumentosCache.length === 0) return;
    try {
        const ids = todosDocumentosCache.map(doc => doc.idCard);
        const res = await chamarApi('/documento/gerar-lote', 'POST', { ids });
        if (!res.erro) {
            window.location.href = `../../saas/Dependencies/download.php?file=${res.file}`;
            carregarDadosDashboard(); carregarProducao();
        }
    } catch (e) { console.error(e); }
}

async function marcarComoEntregueLote() {
    if (todosDocumentosCache.length === 0) return;
    if (!confirm(`🚚 Entregar TODOS?`)) return;
    try {
        await Promise.all(todosDocumentosCache.map(doc => chamarApi(`/documento/status/${doc.idCard}`, 'PUT', { novoStatus: 8 })));
        carregarDadosDashboard(); carregarProducao();
    } catch (e) { console.error(e); }
}

// 9. Avançar todos para Produzidos sem gerar lote (Em Produção)
async function avancarTodosSemLote() {
    if (todosDocumentosCache.length === 0) return;
    if (!confirm(`✅ Avançar TODOS para Produzidos sem gerar lote ZIP?`)) return;
    try {
        await Promise.all(todosDocumentosCache.map(doc => chamarApi(`/documento/status/${doc.idCard}`, 'PUT', { novoStatus: 7 })));
        carregarDadosDashboard(); carregarProducao();
    } catch (e) { console.error(e); }
}

// 8. Filtros de Status (Cards)
function setFilter(statusId) {
    document.getElementById('filtroStatus').value = statusId;
    document.querySelectorAll('.filter-card').forEach(c => c.classList.remove('active'));
    const cardAtivo = document.querySelector(`.filter-card[data-status="${statusId}"]`);
    if (cardAtivo) cardAtivo.classList.add('active');

    const btnMover         = document.getElementById('btnMoverProducao');
    const btnZip           = document.getElementById('btnGerarLote');
    const btnAvancarTodos  = document.getElementById('btnAvancarTodos');
    const btnEntregar      = document.getElementById('btnFinalizarEntrega');

    if (btnMover)        btnMover.style.display        = (statusId == 5) ? 'block' : 'none';
    if (btnZip)          btnZip.style.display          = (statusId == 6) ? 'block' : 'none';
    if (btnAvancarTodos) btnAvancarTodos.style.display = (statusId == 6) ? 'block' : 'none';
    if (btnEntregar)     btnEntregar.style.display     = (statusId == 7) ? 'block' : 'none';

    const labels = { 9: 'CRIADOS', 5: 'SOLICITADOS', 6: 'EM PRODUÇÃO', 7: 'PRODUZIDOS', 8: 'ENTREGUES' };
    document.getElementById('tituloStatus').innerHTML = `<i class="fas fa-list"></i> EXIBINDO: ${labels[statusId]}`;
    carregarProducao();
}

document.addEventListener('DOMContentLoaded', () => { carregarDadosDashboard(); setFilter(5); });