/**
 * entregues.js — Documentos Entregues (lado Instituição)
 * COM paginação, ordenação e busca local
 */
let todosEntregues = [];
let paginaAtualE = 1;
let itensPorPaginaE = 10;
let ordemAscendenteE = true;
let ultimaColunaSorteadaE = 'idCard'; // ordena por idCard por padrão

// Formatação de Data + Hora (padrão celula-data — data em cima, hora embaixo)
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
 * Carrega a lista de documentos entregues (Status 8)
 */
async function carregarEntregues() {
    const user = obterUsuario();
    const corpo = document.getElementById('tabela_entregues_corpo');
    corpo.innerHTML = '<tr><td colspan="11" class="text-center" style="padding:40px; color:#a0aec0;"><i class="fas fa-spinner fa-spin"></i> Carregando...</td></tr>';

    try {
        const res = await chamarApi(`/documento/listar-por-status/${user.idInstituicao}/8`);

        if (!res.erro && res.dados.length > 0) {
            todosEntregues = res.dados;
            // Ordenar por idCard crescente por padrão
            todosEntregues.sort((a, b) => parseInt(a.idCard) - parseInt(b.idCard));
            paginaAtualE = 1;
            renderizarTabelaEntregues();
        } else {
            todosEntregues = [];
            corpo.innerHTML = `<tr><td colspan="11" class="text-center" style="padding:40px; color:#a0aec0;"><i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:10px;"></i>Nenhum documento entregue encontrado.</td></tr>`;
            renderizarPaginadorEntregues(0);
            const contador = document.getElementById('contadorEntregues');
            if (contador) contador.textContent = '';
        }
    } catch (e) { console.error(e); }
}

/**
 * Renderiza a tabela com paginação e busca
 */
function renderizarTabelaEntregues() {
    const corpo = document.getElementById('tabela_entregues_corpo');
    const busca = document.getElementById('busca_entregues').value.toLowerCase().trim();

    // Atualiza ícones de ordenação (usa id="esort-COLUNA")
    document.querySelectorAll('#tabela_entregues_corpo').length; // garante que o DOM está pronto
    document.querySelectorAll('th[data-col] i.fas').forEach(icon => {
        const col = icon.id.replace('esort-', '');
        if (col === ultimaColunaSorteadaE) {
            icon.className = ordemAscendenteE ? 'fas fa-sort-up' : 'fas fa-sort-down';
            icon.style.opacity = '1';
            icon.style.color = '#3182ce';
        } else {
            icon.className = 'fas fa-sort';
            icon.style.opacity = '0.3';
            icon.style.color = '#cbd5e0';
        }
    });

    // Filtro por texto
    const filtrados = todosEntregues.filter(doc => {
        return `${doc.idCard} ${doc.NomeDocumento} ${doc.InsEnsinoDocumento} ${doc.nCPF} ${doc.nRGDocumento}`.toLowerCase().includes(busca);
    });

    // Paginação
    const total = filtrados.length;
    if (paginaAtualE > Math.ceil(total / itensPorPaginaE)) paginaAtualE = 1;
    const inicio = (paginaAtualE - 1) * itensPorPaginaE;
    const listaPaginada = filtrados.slice(inicio, inicio + itensPorPaginaE);

    // Atualiza contador
    const contador = document.getElementById('contadorEntregues');
    if (contador) {
        const fim = Math.min(inicio + itensPorPaginaE, total);
        contador.textContent = total > 0 ? `Mostrando ${inicio + 1}–${fim} de ${total} registro(s)` : '';
    }

    let html = "";
    if (listaPaginada.length > 0) {
        listaPaginada.forEach(d => {
            const dSolicitacao = formatarDataHoraBR(d.dataCriacao);
            const dAlteracao   = formatarDataHoraBR(d.dataAlteracao || d.updated_at || d.data_atualizacao);

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
                    <td class="text-center">${dSolicitacao}</td>
                    <td class="text-center">${dAlteracao}</td>
                    <td>
                        <button class="btn-segunda-via" onclick="pedirSegundaVia('${d.idCard}')">
                            <i class="fas fa-redo"></i> 2ª VIA
                        </button>
                    </td>
                </tr>`;
        });
    } else {
        html = '<tr><td colspan="11" class="text-center" style="padding:40px; color:#a0aec0;">Nenhum registro encontrado.</td></tr>';
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
        // Datas
        if (coluna === 'dataCriacao' || coluna === 'dataAlteracao' || coluna === 'dataNascDocumento') {
            return ordemAscendenteE
                ? String(valA).localeCompare(String(valB))
                : String(valB).localeCompare(String(valA));
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
 * Altera quantidade de itens por página
 */
function alterarItensPorPaginaE(valor) {
    itensPorPaginaE = parseInt(valor);
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