/**
 * Carrega a lista de documentos entregues (Status 8)
 */
async function carregarEntregues() {
    const user = obterUsuario();
    const corpo = document.getElementById('tabela_entregues_corpo');
    
    try {
        // Usamos a mesma rota de listar-por-status, mas fixo no ID 8
        const res = await chamarApi(`/documento/listar-por-status/${user.idInstituicao}/8`);
        
        if (!res.erro && res.dados.length > 0) {
            let html = "";
            res.dados.forEach(d => {
                // Ajustando a data de criação ou atualização como data de entrega
                const dEntrega = d.dataCriacao.split(' ')[0].split('-').reverse().join('/');

                html += `
                    <tr>
                        <td><strong>${d.idCard}</strong></td>
                        <td style="font-weight:700;">${d.NomeDocumento.toUpperCase()}</td>
                        <td>${d.InsEnsinoDocumento}</td>
                        <td>${d.nCPF}</td>
                        <td><img src="../../${d.fotoDocumento}" class="img-table-thumb"></td>
                        <td>${dEntrega}</td>
                        <td>
                            <button class="btn-segunda-via" onclick="pedirSegundaVia('${d.idCard}')">
                                <i class="fas fa-redo"></i> 2ª VIA
                            </button>
                        </td>
                    </tr>`;
            });
            corpo.innerHTML = html;
        } else {
            corpo.innerHTML = `<tr><td colspan="7" class="text-center" style="padding:40px;">Nenhum documento entregue encontrado.</td></tr>`;
        }
    } catch (e) { console.error(e); }
}

/**
 * Reseta o status para Criado (9) para permitir nova solicitação
 */
async function pedirSegundaVia(idCard) {
    if (!confirm(`Deseja solicitar uma SEGUNDA VIA para o documento ${idCard}?`)) return;

    try {
        const res = await chamarApi(`/documento/status/${idCard}`, 'PUT', { novoStatus: 9 });
        if (!res.erro) {
            alert("✅ Documento enviado para 'Criados'. Agora você pode editá-lo ou solicitá-lo novamente.");
            carregarEntregues(); // Recarrega a lista
        } else {
            alert("Erro: " + res.message);
        }
    } catch (e) { console.error(e); }
}

/**
 * Busca local rápida
 */
function filtrarTabelaLocal() {
    const filter = document.getElementById('busca_entregues').value.toLowerCase();
    const linhas = document.getElementById('tabela_entregues_corpo').getElementsByTagName('tr');
    for (let i = 0; i < linhas.length; i++) {
        const txt = linhas[i].innerText.toLowerCase();
        linhas[i].style.display = txt.includes(filter) ? "" : "none";
    }
}

document.addEventListener('DOMContentLoaded', carregarEntregues);