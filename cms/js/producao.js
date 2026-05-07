/**
 * producao.js — Gestão de Produção (lado Instituição)
 * COM paginação, ordenação e busca local
 */
let statusAtual = null;
let todosDocumentos = [];
let paginaAtual = 1;
const itensPorPagina = 10;
let ordemAscendente = true;
let ultimaColunaSorteada = '';

// Formatação de Data BR
function formatarDataBR(dataString) {
    if (!dataString || dataString === '0000-00-00 00:00:00') return '--/--/----';
    return dataString.split(' ')[0].split('-').reverse().join('/');
}

/**
 * Carrega os números nos cards
 */
async function carregarCardsProducao() {
    const user = obterUsuario();
    try {
        const res = await chamarApi(`/documento/resumo-dashboard/${user.idInstituicao}`);
        if (!res.erro) {
            const d = res.dados;
            const container = document.getElementById('container-estatisticas');
            
            container.innerHTML = `
                <section class="stats-grid">
                    <div class="card" onclick="filtrarProducao(9, 'Docs. Criados')" style="cursor:pointer; border-left: 5px solid #0dcaf0;">
                        <div class="card-info"><h3>${d.criados || 0}</h3><p>Criados</p></div>
                    </div>
                    <div class="card" onclick="filtrarProducao(5, 'Solicitados')" style="cursor:pointer; border-left: 5px solid #ffc107;">
                        <div class="card-info"><h3>${d.solicitados || 0}</h3><p>Solicitados</p></div>
                    </div>
                    <div class="card" onclick="filtrarProducao(6, 'Em Produção')" style="cursor:pointer; border-left: 5px solid #0d6efd;">
                        <div class="card-info"><h3>${d.producao || 0}</h3><p>Em Produção</p></div>
                    </div>
                    <div class="card" onclick="filtrarProducao(7, 'Produzidos')" style="cursor:pointer; border-left: 5px solid #198754;">
                        <div class="card-info"><h3>${d.produzidos || 0}</h3><p>Produzidos</p></div>
                    </div>
                </section>
            `;
        }
    } catch (e) { console.error(e); }
}

/**
 * Filtra por status e armazena no cache para paginação
 */
async function filtrarProducao(idStatus, label) {
    statusAtual = idStatus;
    const user = obterUsuario();
    const tituloLista = document.getElementById('titulo-lista');
    
    try {
        const res = await chamarApi(`/documento/listar-por-status/${user.idInstituicao}/${idStatus}`);
        
        if (!res.erro && res.dados.length > 0) {
            todosDocumentos = res.dados;
            paginaAtual = 1;
            tituloLista.innerHTML = `<i class="fas fa-list"></i> EXIBINDO: ${label.toUpperCase()}`;
            renderizarTabela();
        } else {
            todosDocumentos = [];
            const corpo = document.getElementById('tabela_producao_corpo');
            corpo.innerHTML = `<tr><td colspan="10" class="text-center" style="padding:40px;">Nenhum documento encontrado.</td></tr>`;
            tituloLista.innerHTML = `<i class="fas fa-list"></i> EXIBINDO: ${label.toUpperCase()}`;
            renderizarPaginador(0);
        }
    } catch (e) { console.error(e); }
}

/**
 * Renderiza a tabela com paginação e busca
 */
function renderizarTabela() {
    const corpo = document.getElementById('tabela_producao_corpo');
    const busca = document.getElementById('busca_producao').value.toLowerCase().trim();
    const user = obterUsuario();
    const idAcl = parseInt(user.idAcl);

    // Atualiza ícones de ordenação
    document.querySelectorAll('th[data-col] i.fas').forEach(icon => {
        const col = icon.dataset.sort;
        if (col === ultimaColunaSorteada) {
            icon.className = ordemAscendente ? 'fas fa-sort-up' : 'fas fa-sort-down';
            icon.style.opacity = '1';
        } else {
            icon.className = 'fas fa-sort';
            icon.style.opacity = '0.3';
        }
    });

    // Filtro por texto
    const filtrados = todosDocumentos.filter(doc => {
        return `${doc.idCard} ${doc.NomeDocumento} ${doc.InsEnsinoDocumento} ${doc.nCPF} ${doc.nRGDocumento}`.toLowerCase().includes(busca);
    });

    // Paginação
    const inicio = (paginaAtual - 1) * itensPorPagina;
    const listaPaginada = filtrados.slice(inicio, inicio + itensPorPagina);

    let html = "";
    if (listaPaginada.length > 0) {
        listaPaginada.forEach(d => {
            const dNasc = d.dataNascDocumento ? d.dataNascDocumento.split('-').reverse().join('/') : '--/--/----';
            const dCriacao = formatarDataBR(d.dataCriacao);

            // Lógica de ação baseada na ACL
            let acaoHTML = "";
            if (idAcl < 3) {
                acaoHTML = `<button class="btn-sucesso" onclick="avancarStatus('${d.idCard}', ${statusAtual})">AVANÇAR <i class="fas fa-arrow-right"></i></button>`;
            } else if (statusAtual == 9) {
                acaoHTML = `<button class="btn-sucesso" onclick="avancarStatus('${d.idCard}', ${statusAtual})"><i class="fas fa-paper-plane"></i> SOLICITAR</button>`;
            } else {
                acaoHTML = `<span style="color: #a0aec0; font-size: 10px; font-weight: bold;"><i class="fas fa-lock"></i> EM ANÁLISE</span>`;
            }

            html += `
                <tr>
                    <td><strong>${d.idCard}</strong></td>
                    <td class="text-bold">${d.NomeDocumento.toUpperCase()}</td>
                    <td>${d.InsEnsinoDocumento}</td>
                    <td>${d.serieDocumento}</td>
                    <td>${d.nCPF}</td>
                    <td>${d.nRGDocumento}</td>
                    <td>${dNasc}</td>
                    <td><img src="../../${d.fotoDocumento}" class="img-table-thumb"></td>
                    <td>${dCriacao}</td>
                    <td><div style="display: flex; justify-content: center;">${acaoHTML}</div></td>
                </tr>`;
        });
    } else {
        html = '<tr><td colspan="10" class="text-center" style="padding:40px;">Nenhum registro encontrado.</td></tr>';
    }

    corpo.innerHTML = html;
    renderizarPaginador(filtrados.length);
}

/**
 * Paginador (padrão instituicoes.php)
 */
function renderizarPaginador(totalItens) {
    const container = document.getElementById('paginador-producao');
    if (!container) return;
    container.innerHTML = "";

    const totalPaginas = Math.ceil(totalItens / itensPorPagina);
    if (totalPaginas <= 1) return;

    let maxBotoes = 5;
    let inicio = Math.max(1, paginaAtual - Math.floor(maxBotoes / 2));
    let fim = Math.min(totalPaginas, inicio + maxBotoes - 1);

    // Botões "PRIMEIRA" e "ANTERIOR"
    if (paginaAtual > 1) {
        container.appendChild(criarBotaoPagP(1, '<i class="fas fa-angle-double-left"></i>', false));
        container.appendChild(criarBotaoPagP(paginaAtual - 1, '<i class="fas fa-angle-left"></i>', false));
    }

    // Números
    for (let i = inicio; i <= fim; i++) {
        container.appendChild(criarBotaoPagP(i, i, i === paginaAtual));
    }

    // Botões "PRÓXIMA" e "ÚLTIMA"
    if (paginaAtual < totalPaginas) {
        container.appendChild(criarBotaoPagP(paginaAtual + 1, '<i class="fas fa-angle-right"></i>', false));
        container.appendChild(criarBotaoPagP(totalPaginas, '<i class="fas fa-angle-double-right"></i>', false));
    }
}

function criarBotaoPagP(pagina, html, ativo) {
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

function mudarPagina(num) {
    paginaAtual = num;
    renderizarTabela();
    window.scrollTo({ top: 300, behavior: 'smooth' });
}

/**
 * Ordenação de colunas
 */
function ordenarProducao(coluna) {
    if (ultimaColunaSorteada === coluna) {
        ordemAscendente = !ordemAscendente;
    } else {
        ordemAscendente = true;
        ultimaColunaSorteada = coluna;
    }

    todosDocumentos.sort((a, b) => {
        let valA = a[coluna] || '';
        let valB = b[coluna] || '';

        if (coluna === 'idCard') {
            return ordemAscendente ? (parseInt(valA) - parseInt(valB)) : (parseInt(valB) - parseInt(valA));
        }

        valA = valA.toString().toLowerCase();
        valB = valB.toString().toLowerCase();

        if (valA < valB) return ordemAscendente ? -1 : 1;
        if (valA > valB) return ordemAscendente ? 1 : -1;
        return 0;
    });

    paginaAtual = 1;
    renderizarTabela();
}

/**
 * Busca local com paginação
 */
function filtrarTabelaLocal() {
    paginaAtual = 1;
    renderizarTabela();
}

/**
 * Avança o status do documento
 */
async function avancarStatus(idCard, statusDeOrigem) {
    let proximoStatus = 5;

    if (statusDeOrigem == 5) proximoStatus = 6;
    else if (statusDeOrigem == 6) proximoStatus = 7;
    else if (statusDeOrigem == 7) proximoStatus = 8;

    if (!confirm(`Deseja alterar o status do documento ${idCard}?`)) return;

    try {
        const res = await chamarApi(`/documento/status/${idCard}`, 'PUT', { novoStatus: proximoStatus });
        if (!res.erro) {
            // Remove do cache local e re-renderiza
            todosDocumentos = todosDocumentos.filter(doc => doc.idCard !== idCard);
            renderizarTabela();
            carregarCardsProducao();
        } else {
            alert("Erro: " + res.message);
        }
    } catch (e) { console.error(e); }
}

document.addEventListener('DOMContentLoaded', carregarCardsProducao);