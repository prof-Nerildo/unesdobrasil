/**
 * producao.js — Gestão de Produção (lado Instituição)
 * COM paginação, ordenação e busca local
 */
let statusAtual = null;
let todosDocumentos = [];
let paginaAtual = 1;
let itensPorPagina = 10;
let ordemAscendente = true;
let ultimaColunaSorteada = '';

// Formatação de Data BR (simples — sem hora)
function formatarDataBR(dataString) {
    if (!dataString || dataString === '0000-00-00 00:00:00') return '--/--/----';
    return dataString.split(' ')[0].split('-').reverse().join('/');
}

// Formatação de Data + Hora (padrão UNES — data em cima, hora embaixo)
function formatarDataHoraBR(dataString) {
    if (!dataString || dataString === '0000-00-00 00:00:00') return '<span class="celula-data"><span class="data-dt">--/--/----</span><span class="hora-dt">--:--:--</span></span>';
    const data = new Date(dataString);
    const dia    = String(data.getDate()).padStart(2, '0');
    const mes    = String(data.getMonth() + 1).padStart(2, '0');
    const ano    = data.getFullYear();
    const hora   = String(data.getHours()).padStart(2, '0');
    const minuto = String(data.getMinutes()).padStart(2, '0');
    const seg    = String(data.getSeconds()).padStart(2, '0');
    return `<span class="celula-data"><span class="data-dt">${dia}/${mes}/${ano}</span><span class="hora-dt">${hora}:${minuto}:${seg}</span></span>`;
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
                    <div class="card card-status" onclick="filtrarProducao(9, 'Docs. Criados')" style="cursor:pointer; border-left: 5px solid #0dcaf0;">
                        <div class="card-icon-status" style="color:#0dcaf0;"><i class="fas fa-plus-circle"></i></div>
                        <div class="card-info">
                            <h3>${d.criados || 0}</h3>
                            <p>Criados</p>
                        </div>
                    </div>
                    <div class="card card-status" onclick="filtrarProducao(5, 'Solicitados')" style="cursor:pointer; border-left: 5px solid #ffc107;">
                        <div class="card-icon-status" style="color:#ffc107;"><i class="fas fa-paper-plane"></i></div>
                        <div class="card-info">
                            <h3>${d.solicitados || 0}</h3>
                            <p>Solicitados</p>
                        </div>
                    </div>
                    <div class="card card-status" onclick="filtrarProducao(6, 'Em Produção')" style="cursor:pointer; border-left: 5px solid #0d6efd;">
                        <div class="card-icon-status" style="color:#0d6efd;"><i class="fas fa-tools"></i></div>
                        <div class="card-info">
                            <h3>${d.producao || 0}</h3>
                            <p>Em Produção</p>
                        </div>
                    </div>
                    <div class="card card-status" onclick="filtrarProducao(7, 'Produzidos')" style="cursor:pointer; border-left: 5px solid #198754;">
                        <div class="card-icon-status" style="color:#198754;"><i class="fas fa-id-card"></i></div>
                        <div class="card-info">
                            <h3>${d.produzidos || 0}</h3>
                            <p>Produzidos</p>
                        </div>
                    </div>
                    <div class="card card-status" onclick="window.location.href='entregues.php'" style="cursor:pointer; border-left: 5px solid #212529;">
                        <div class="card-icon-status" style="color:#212529;"><i class="fas fa-shipping-fast"></i></div>
                        <div class="card-info">
                            <h3>${d.entregues || 0}</h3>
                            <p>Entregues</p>
                        </div>
                    </div>
                </section>
                <style>
                    .card-status { display: flex; align-items: center; gap: 15px; }
                    .card-status:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
                    .card-icon-status { font-size: 1.8rem; min-width: 40px; text-align: center; opacity: 0.85; }
                    .card-info h3 { font-size: 1.8rem; font-weight: 800; margin: 0; color: #2d3748; }
                    .card-info p { margin: 0; font-size: 0.72rem; font-weight: 700; color: #718096; text-transform: uppercase; letter-spacing: 0.05em; }
                </style>
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
            corpo.innerHTML = `<tr><td colspan="11" class="text-center" style="padding:40px; color:#a0aec0;"><i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:10px;"></i>Nenhum documento encontrado neste status.</td></tr>`;
            tituloLista.innerHTML = `<i class="fas fa-list"></i> EXIBINDO: ${label.toUpperCase()}`;
            renderizarPaginador(0);
            const contador = document.getElementById('contadorProd');
            if (contador) contador.textContent = '';
        }
    } catch (e) { console.error(e); }
}

/**
 * Renderiza a tabela com paginação e busca
 */
function renderizarTabela() {
    const corpo = document.getElementById('tabela_producao_corpo');
    const busca = document.getElementById('busca_producao').value.toLowerCase().trim();

    // Atualiza ícones de ordenação (usa id="psort-COLUNA")
    document.querySelectorAll('th[data-col] i.fas').forEach(icon => {
        const col = icon.id.replace('psort-', '');
        if (col === ultimaColunaSorteada) {
            icon.className = ordemAscendente ? 'fas fa-sort-up' : 'fas fa-sort-down';
            icon.style.opacity = '1';
            icon.style.color = '#3182ce';
        } else {
            icon.className = 'fas fa-sort';
            icon.style.opacity = '0.3';
            icon.style.color = '#cbd5e0';
        }
    });

    // Filtro por texto
    const filtrados = todosDocumentos.filter(doc => {
        return `${doc.idCard} ${doc.NomeDocumento} ${doc.InsEnsinoDocumento} ${doc.nCPF} ${doc.nRGDocumento}`.toLowerCase().includes(busca);
    });

    const total = filtrados.length;
    if (paginaAtual > Math.ceil(total / itensPorPagina)) paginaAtual = 1;
    const inicio = (paginaAtual - 1) * itensPorPagina;
    const listaPaginada = filtrados.slice(inicio, inicio + itensPorPagina);

    // Atualiza contador
    const contador = document.getElementById('contadorProd');
    if (contador) {
        const fim = Math.min(inicio + itensPorPagina, total);
        contador.textContent = total > 0 ? `Mostrando ${inicio + 1}–${fim} de ${total} registro(s)` : '';
    }

    let html = "";
    if (listaPaginada.length > 0) {
        listaPaginada.forEach(d => {
            const dNasc    = d.dataNascDocumento ? d.dataNascDocumento.split('-').reverse().join('/') : '--/--/----';
            const dCriacao    = formatarDataHoraBR(d.dataCriacao);
            const dAlteracao  = formatarDataHoraBR(d.dataAlteracao || d.updated_at || d.data_atualizacao);

            // Botão de ação baseado no STATUS atual (não na ACL)
            let acaoHTML = "";
            if (statusAtual == 9) {
                // Criados: pode SOLICITAR
                acaoHTML = `<button class="btn-sucesso" onclick="avancarStatus('${d.idCard}', ${statusAtual})"><i class="fas fa-paper-plane"></i> SOLICITAR</button>`;
            } else if (statusAtual == 5) {
                // Solicitados: aguardando UNES
                acaoHTML = `<span class="badge-analise"><i class="fas fa-hourglass-half"></i> AGUARDANDO</span>`;
            } else if (statusAtual == 6) {
                // Em Produção: aguardando UNES
                acaoHTML = `<span class="badge-analise" style="background:#e8f4fd; color:#0d6efd;"><i class="fas fa-tools"></i> EM PRODUÇÃO</span>`;
            } else if (statusAtual == 7) {
                // Produzidos: aguardando entrega
                acaoHTML = `<span class="badge-analise" style="background:#f0fdf4; color:#198754;"><i class="fas fa-id-card"></i> PRODUZIDO</span>`;
            } else {
                acaoHTML = `<span class="badge-analise"><i class="fas fa-lock"></i> BLOQUEADO</span>`;
            }

            html += `
                <tr>
                    <td><strong>${d.idCard}</strong></td>
                    <td class="text-bold">${d.NomeDocumento.toUpperCase()}</td>
                    <td>${d.InsEnsinoDocumento}</td>
                    <td>${d.serieDocumento}</td>
                    <td>${d.nCPF}</td>
                    <td>${d.nRGDocumento || '--'}</td>
                    <td>${dNasc}</td>
                    <td><img src="../../${d.fotoDocumento}" class="img-table-thumb"></td>
                    <td class="text-center">${dCriacao}</td>
                    <td class="text-center">${dAlteracao}</td>
                    <td><div style="display: flex; justify-content: center;">${acaoHTML}</div></td>
                </tr>`;
        });
    } else {
        html = '<tr><td colspan="11" class="text-center" style="padding:40px;">Nenhum registro encontrado.</td></tr>';
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

        // Ordenação por campos de data (compara string ISO diretamente)
        if (coluna === 'dataCriacao' || coluna === 'dataAlteracao' || coluna === 'dataNascDocumento') {
            return ordemAscendente
                ? String(valA).localeCompare(String(valB))
                : String(valB).localeCompare(String(valA));
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

/**
 * Altera quantidade de itens por página
 */
function alterarItensPorPaginaProd(valor) {
    itensPorPagina = parseInt(valor);
    paginaAtual = 1;
    renderizarTabela();
}