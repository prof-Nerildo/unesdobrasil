/**
 * entregues.js — Documentos Entregues (lado Instituição)
 * COM paginação, ordenação e busca local
 */
let todosEntregues = [];
let paginaAtualE = 1;
const itensPorPaginaE = 10;
let ordemAscendenteE = true;
let ultimaColunaSorteadaE = '';

// Formatação de Data e Hora BR
function formatarDataHoraBR(dataString) {
    if (!dataString || dataString === '0000-00-00 00:00:00') return '--/--/----';
    const data = new Date(dataString);
    const dia = String(data.getDate()).padStart(2, '0');
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const ano = data.getFullYear();
    const hora = String(data.getHours()).padStart(2, '0');
    const minuto = String(data.getMinutes()).padStart(2, '0');
    return `${dia}/${mes}/${ano} <br> <small style="color: #a0aec0;">${hora}:${minuto}</small>`;
}

/**
 * Carrega a lista de documentos entregues (Status 8)
 */
async function carregarEntregues() {
    const user = obterUsuario();
    const corpo = document.getElementById('tabela_entregues_corpo');
    corpo.innerHTML = '<tr><td colspan="10" class="text-center" style="padding:40px; color:#a0aec0;"><i class="fas fa-spinner fa-spin"></i> Carregando...</td></tr>';
    
    try {
        const res = await chamarApi(`/documento/listar-por-status/${user.idInstituicao}/8`);
        
        if (!res.erro && res.dados.length > 0) {
            todosEntregues = res.dados;
            paginaAtualE = 1;
            renderizarTabelaEntregues();
        } else {
            todosEntregues = [];
            corpo.innerHTML = `<tr><td colspan="10" class="text-center" style="padding:40px;">Nenhum documento entregue encontrado.</td></tr>`;
            renderizarPaginadorEntregues(0);
        }
    } catch (e) { console.error(e); }
}

/**
 * Renderiza a tabela com paginação e busca
 */
function renderizarTabelaEntregues() {
    const corpo = document.getElementById('tabela_entregues_corpo');
    const busca = document.getElementById('busca_entregues').value.toLowerCase().trim();

    // Filtro por texto
    const filtrados = todosEntregues.filter(doc => {
        return `${doc.idCard} ${doc.NomeDocumento} ${doc.InsEnsinoDocumento} ${doc.nCPF} ${doc.nRGDocumento}`.toLowerCase().includes(busca);
    });

    // Paginação
    const inicio = (paginaAtualE - 1) * itensPorPaginaE;
    const listaPaginada = filtrados.slice(inicio, inicio + itensPorPaginaE);

    let html = "";
    if (listaPaginada.length > 0) {
        listaPaginada.forEach(d => {
            const dSolicitacao = formatarDataHoraBR(d.dataCriacao);
            const dAlteracao = formatarDataHoraBR(d.dataAlteracao || d.updated_at || d.data_atualizacao);

            html += `
                <tr>
                    <td><strong style="color:#3182ce;">${d.idCard}</strong></td>
                    <td style="font-weight:700;">${d.NomeDocumento.toUpperCase()}</td>
                    <td style="font-size:11px; color:#718096;">${d.InsEnsinoDocumento}</td>
                    <td>${d.serieDocumento || '--'}</td>
                    <td>${d.nCPF}</td>
                    <td>${d.nRGDocumento || '--'}</td>
                    <td>${d.dataNascDocumento ? d.dataNascDocumento.split('-').reverse().join('/') : '--'}</td>
                    <td class="text-center"><img src="../../${d.fotoDocumento}" class="img-table-thumb"></td>
                    <td class="text-center" style="font-size:12px;">${dSolicitacao}</td>
                    <td class="text-center" style="font-size:12px;">${dAlteracao}</td>
                    <td>
                        <button class="btn-segunda-via" onclick="pedirSegundaVia('${d.idCard}')">
                            <i class="fas fa-redo"></i> 2ª VIA
                        </button>
                    </td>
                </tr>`;
        });
    } else {
        html = '<tr><td colspan="10" class="text-center" style="padding:40px;">Nenhum registro encontrado.</td></tr>';
    }

    corpo.innerHTML = html;
    renderizarPaginadorEntregues(filtrados.length);
}

/**
 * Paginador (padrão instituicoes.php)
 */
function renderizarPaginadorEntregues(totalItens) {
    const container = document.getElementById('paginador-entregues');
    if (!container) return;
    container.innerHTML = "";

    const totalPaginas = Math.ceil(totalItens / itensPorPaginaE);
    if (totalPaginas <= 1) return;

    let maxBotoes = 5;
    let inicio = Math.max(1, paginaAtualE - Math.floor(maxBotoes / 2));
    let fim = Math.min(totalPaginas, inicio + maxBotoes - 1);

    // Botões "PRIMEIRA" e "ANTERIOR"
    if (paginaAtualE > 1) {
        container.appendChild(criarBotaoPagE(1, '<i class="fas fa-angle-double-left"></i>', false));
        container.appendChild(criarBotaoPagE(paginaAtualE - 1, '<i class="fas fa-angle-left"></i>', false));
    }

    // Números
    for (let i = inicio; i <= fim; i++) {
        container.appendChild(criarBotaoPagE(i, i, i === paginaAtualE));
    }

    // Botões "PRÓXIMA" e "ÚLTIMA"
    if (paginaAtualE < totalPaginas) {
        container.appendChild(criarBotaoPagE(paginaAtualE + 1, '<i class="fas fa-angle-right"></i>', false));
        container.appendChild(criarBotaoPagE(totalPaginas, '<i class="fas fa-angle-double-right"></i>', false));
    }
}

function criarBotaoPagE(pagina, html, ativo) {
    const btn = document.createElement('button');
    btn.innerHTML = html;
    btn.className = `btn-pag ${ativo ? 'active' : ''}`;
    btn.onclick = () => {
        paginaAtualE = pagina;
        renderizarTabelaEntregues();
        window.scrollTo({ top: 200, behavior: 'smooth' });
    };
    return btn;
}

function mudarPaginaE(num) {
    paginaAtualE = num;
    renderizarTabelaEntregues();
    window.scrollTo({ top: 200, behavior: 'smooth' });
}

/**
 * Ordenação de colunas
 */
function ordenarEntregues(coluna) {
    if (ultimaColunaSorteadaE === coluna) {
        ordemAscendenteE = !ordemAscendenteE;
    } else {
        ordemAscendenteE = true;
        ultimaColunaSorteadaE = coluna;
    }

    todosEntregues.sort((a, b) => {
        let valA = a[coluna] || '';
        let valB = b[coluna] || '';

        if (coluna === 'idCard') {
            return ordemAscendenteE ? (parseInt(valA) - parseInt(valB)) : (parseInt(valB) - parseInt(valA));
        }

        valA = valA.toString().toLowerCase();
        valB = valB.toString().toLowerCase();

        if (valA < valB) return ordemAscendenteE ? -1 : 1;
        if (valA > valB) return ordemAscendenteE ? 1 : -1;
        return 0;
    });

    paginaAtualE = 1;
    renderizarTabelaEntregues();
}

/**
 * Busca com paginação
 */
function filtrarTabelaLocal() {
    paginaAtualE = 1;
    renderizarTabelaEntregues();
}

/**
 * Solicita segunda via (reseta para status Criado)
 */
async function pedirSegundaVia(idCard) {
    if (!confirm(`Deseja solicitar uma SEGUNDA VIA para o documento ${idCard}?`)) return;

    try {
        const res = await chamarApi(`/documento/status/${idCard}`, 'PUT', { novoStatus: 9 });
        if (!res.erro) {
            alert("✅ Documento enviado para 'Criados'. Agora você pode editá-lo ou solicitá-lo novamente.");
            todosEntregues = todosEntregues.filter(doc => doc.idCard !== idCard);
            renderizarTabelaEntregues();
        } else {
            alert("Erro: " + res.message);
        }
    } catch (e) { console.error(e); }
}

document.addEventListener('DOMContentLoaded', carregarEntregues);