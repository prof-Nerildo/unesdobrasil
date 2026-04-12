let statusAtual = null;

/**
 * Carrega os números nos cards com IDs reais do banco
 */
async function carregarCardsProducao() {
    const user = obterUsuario();
    try {
        const res = await chamarApi(`/documento/resumo-dashboard/${user.idInstituicao}`);
        if (!res.erro) {
            const d = res.dados;
            const container = document.getElementById('container-estatisticas');
            
            // Note os nomes: d.criados, d.solicitados, d.producao, d.produzidos
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
 * Filtra a tabela e controla a exibição do botão conforme ACL e Status
 */
async function filtrarProducao(idStatus, label) {
    statusAtual = idStatus;
    const user = obterUsuario();
    const idAcl = parseInt(user.idAcl);
    const tituloLista = document.getElementById('titulo-lista');
    
    try {
        const res = await chamarApi(`/documento/listar-por-status/${user.idInstituicao}/${idStatus}`);
        const corpo = document.getElementById('tabela_producao_corpo');
        
        if (!res.erro && res.dados.length > 0) {
            let html = "";
            res.dados.forEach(d => {
                const dNasc = d.dataNascDocumento.split('-').reverse().join('/');
                const dCriacao = d.dataCriacao.split(' ')[0].split('-').reverse().join('/');

                // LÓGICA DE AÇÃO BASEADA NO SEU PRINT
                let acaoHTML = "";
                
                if (idAcl < 3) {
                    // CENTRAL UNES: Sempre pode avançar qualquer status
                    acaoHTML = `<button class="btn-sucesso" onclick="avancarStatus('${d.idCard}', ${idStatus})">AVANÇAR <i class="fas fa-arrow-right"></i></button>`;
                } else if (idStatus == 9) {
                    // INSTITUIÇÃO: Só vê botão no status 9 (CRIADO)
                    acaoHTML = `<button class="btn-sucesso" onclick="avancarStatus('${d.idCard}', ${idStatus})"><i class="fas fa-paper-plane"></i> SOLICITAR </button>`;
                } else {
                    // BLOQUEIO PARA INSTITUIÇÃO: Após solicitado, apenas visualiza
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
            corpo.innerHTML = html;
            tituloLista.innerHTML = `<i class="fas fa-list"></i> EXIBINDO: ${label.toUpperCase()}`;
        } else {
            corpo.innerHTML = `<tr><td colspan="10" class="text-center" style="padding:40px;">Nenhum documento encontrado.</td></tr>`;
        }
    } catch (e) { console.error(e); }
}

/**
 * Avança o status seguindo fielmente os IDs do seu Banco
 */
async function avancarStatus(idCard, statusDeOrigem) {
    let proximoStatus = 5; // Padrão: Solicitado (5)

    if (statusDeOrigem == 5) proximoStatus = 6;      // Solicitado -> Em Produção
    else if (statusDeOrigem == 6) proximoStatus = 7; // Em Produção -> Produzido
    else if (statusDeOrigem == 7) proximoStatus = 8; // Produzido -> Entregue

    if (!confirm(`Deseja alterar o status do documento ${idCard}?`)) return;

    try {
        const res = await chamarApi(`/documento/status/${idCard}`, 'PUT', { novoStatus: proximoStatus });
        if (!res.erro) {
            carregarCardsProducao();
            filtrarProducao(statusDeOrigem, "Atualizando...");
        } else {
            alert("Erro: " + res.message);
        }
    } catch (e) { console.error(e); }
}

function filtrarTabelaLocal() {
    const filter = document.getElementById('busca_producao').value.toLowerCase();
    const linhas = document.getElementById('tabela_producao_corpo').getElementsByTagName('tr');
    for (let i = 0; i < linhas.length; i++) {
        if (linhas[i].cells.length < 5) continue; 
        const txt = linhas[i].innerText.toLowerCase();
        linhas[i].style.display = txt.includes(filter) ? "" : "none";
    }
}

document.addEventListener('DOMContentLoaded', carregarCardsProducao);