// VARIÁVEIS GLOBAIS
let cropper;
let fotoFinalBase64 = "";
let todosDocumentos = [];
let paginaAtual = 1;
let itensPorPagina = 10;
let ordemDocAsc = true;
let colunaDocSort = '';
let dadosOriginaisInst = { nome: "", curso: "", podeEditarInst: "nao", podeEditarCurso: "nao" };

// Data + Hora no padrão visual UNES
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

// VARIÁVEIS DE CONTROLE DE EDIÇÃO
let modoEdicao = false;
let idCardEdicao = null;

/**
 * Inicialização da Página
 */
async function prepararPagina() {
    const user = obterUsuario();
    if (!user) { window.location.href = '../login.html'; return; }
    try {
        const res = await chamarApi(`/instituicao/detalhes/${user.idInstituicao}`);
        const inst = res.dados;
        if (!inst || inst.idStatus != 2) {
            document.getElementById('container-principal').innerHTML = `<div class="text-center"><h2>Acesso Bloqueado</h2></div>`;
            return;
        }
        dadosOriginaisInst = { 
            nome: inst.label_edita_instituicao || inst.nome_fantasia,
            curso: inst.label_edita_curso || "",
            podeEditarInst: inst.pode_editar_instituicao,
            podeEditarCurso: inst.pode_editar_curso
        };
        aplicarDadosPadrao();
        listarDocumentosCriados();
    } catch (e) { console.error(e); }
}

/**
 * Aplica os dados da instituição nos campos fixos ou liberados
 */
function aplicarDadosPadrao() {
    const campoInst = document.getElementById('nome_escola_fixo');
    const campoCurso = document.getElementById('curso_aluno');
    
    campoInst.value = dadosOriginaisInst.podeEditarInst === 'sim' ? "" : dadosOriginaisInst.nome;
    campoInst.readOnly = dadosOriginaisInst.podeEditarInst !== 'sim';
    
    campoCurso.value = dadosOriginaisInst.podeEditarCurso === 'sim' ? "" : dadosOriginaisInst.curso;
    campoCurso.readOnly = dadosOriginaisInst.podeEditarCurso !== 'sim';
    
    campoInst.style.background = campoInst.readOnly ? "#f8fafc" : "#fff";
    campoCurso.style.background = campoCurso.readOnly ? "#f8fafc" : "#fff";
}

/**
 * Busca documentos com status 'Criado' (9)
 */
async function listarDocumentosCriados() {
    const user = obterUsuario();
    try {
        const res = await chamarApi(`/documento/listar-criados/${user.idInstituicao}`);
        if (!res.erro) { 
            todosDocumentos = res.dados; 
            renderizarTabela(); 
        }
    } catch (e) { console.error(e); }
}

/**
 * Renderiza a tabela com Filtro, Ordenação, Contador e Paginação
 */
function renderizarTabela() {
    const corpo = document.getElementById('tabela_docs_corpo');
    const busca = document.getElementById('busca_documento').value.toLowerCase();

    // Atualiza ícones de ordenação
    document.querySelectorAll('th[data-col] i.fas').forEach(icon => {
        const col = icon.id.replace('dsort-', '');
        if (col === colunaDocSort) {
            icon.className = ordemDocAsc ? 'fas fa-sort-up' : 'fas fa-sort-down';
            icon.style.opacity = '1';
            icon.style.color = '#3182ce';
        } else {
            icon.className = 'fas fa-sort';
            icon.style.opacity = '0.3';
            icon.style.color = '#cbd5e0';
        }
    });

    let filtrados = todosDocumentos.filter(d =>
        (d.NomeDocumento || '').toLowerCase().includes(busca) ||
        (d.nCPF || '').includes(busca) ||
        (d.idCard || '').includes(busca)
    );

    const total = filtrados.length;
    const totalPaginas = Math.ceil(total / itensPorPagina);
    if (paginaAtual > totalPaginas) paginaAtual = 1;
    const inicio = (paginaAtual - 1) * itensPorPagina;
    const listaExibir = filtrados.slice(inicio, inicio + itensPorPagina);

    // Atualiza contador
    const contador = document.getElementById('contadorDocs');
    if (contador) {
        const fim = Math.min(inicio + itensPorPagina, total);
        contador.textContent = total > 0 ? `Mostrando ${inicio + 1}–${fim} de ${total} registro(s)` : '';
    }

    let html = "";
    listaExibir.forEach(d => {
        const dNasc      = d.dataNascDocumento ? d.dataNascDocumento.split('-').reverse().join('/') : '--/--/----';
        const dCriacao   = formatarDataHoraBR(d.dataCriacao);
        const dAlteracao = formatarDataHoraBR(d.dataAlteracao || d.updated_at || d.data_atualizacao);
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
                <td>
                    <button class="btn-edit-table" onclick="abrirEdicaoDoc('${d.idCard}')" title="Editar"><i class="far fa-edit"></i></button>
                    <button class="btn-delete-table" onclick="excluirDoc('${d.idCard}')" title="Excluir"><i class="far fa-trash-alt"></i></button>
                </td>
            </tr>`;
    });
    corpo.innerHTML = html || '<tr><td colspan="11" class="text-center" style="padding:30px; color:#a0aec0;">Nenhum registro encontrado.</td></tr>';
    renderPaginacao(totalPaginas);
}

function renderPaginacao(total) {
    const container = document.getElementById('paginacao_docs');
    let html = "";
    if (total <= 1) { container.innerHTML = ""; return; }
    // Anterior
    if (paginaAtual > 1) html += `<button class="btn-page" onclick="mudarPagina(${paginaAtual - 1})"><i class="fas fa-angle-left"></i></button>`;
    for (let i = 1; i <= total; i++) {
        html += `<button class="btn-page ${i === paginaAtual ? 'active' : ''}" onclick="mudarPagina(${i})">${i}</button>`;
    }
    // Próxima
    if (paginaAtual < total) html += `<button class="btn-page" onclick="mudarPagina(${paginaAtual + 1})"><i class="fas fa-angle-right"></i></button>`;
    container.innerHTML = html;
}

function mudarPagina(p) { paginaAtual = p; renderizarTabela(); }
function filtrarDocumentos() { paginaAtual = 1; renderizarTabela(); }

/**
 * Ordenação de colunas
 */
function ordenarDocs(coluna) {
    if (colunaDocSort === coluna) {
        ordemDocAsc = !ordemDocAsc;
    } else {
        ordemDocAsc = true;
        colunaDocSort = coluna;
    }
    todosDocumentos.sort((a, b) => {
        let valA = a[coluna] || '';
        let valB = b[coluna] || '';
        // Número (idCard)
        if (coluna === 'idCard') {
            return ordemDocAsc ? parseInt(valA) - parseInt(valB) : parseInt(valB) - parseInt(valA);
        }
        // Datas (compara string ISO)
        if (coluna === 'dataCriacao' || coluna === 'dataAlteracao' || coluna === 'dataNascDocumento') {
            return ordemDocAsc
                ? String(valA).localeCompare(String(valB))
                : String(valB).localeCompare(String(valA));
        }
        // Texto
        valA = valA.toString().toLowerCase();
        valB = valB.toString().toLowerCase();
        if (valA < valB) return ordemDocAsc ? -1 : 1;
        if (valA > valB) return ordemDocAsc ? 1 : -1;
        return 0;
    });
    paginaAtual = 1;
    renderizarTabela();
}

/**
 * Altera quantidade de itens por página
 */
function alterarItensPorPaginaDocs(valor) {
    itensPorPagina = parseInt(valor);
    paginaAtual = 1;
    renderizarTabela();
}

/**
 * Limpa o formulário e reseta o Modo Edição
 */
function limparCamposAluno() {
    ['nome_aluno', 'data_nascimento', 'cpf_aluno', 'rg_aluno'].forEach(id => document.getElementById(id).value = "");
    fotoFinalBase64 = "";
    document.getElementById('preview-final').style.display = 'none';
    document.getElementById('wrapper-crop').style.display = 'none';
    document.getElementById('input_foto').value = "";
    if (cropper) cropper.destroy();
    
    // Reseta estados de edição
    modoEdicao = false;
    idCardEdicao = null;
    
    // Volta visual original
    document.querySelector('.top-bar h1').innerHTML = `<i class="fas fa-plus-circle"></i> Criar Documento Estudantil`;
    const btnSucesso = document.querySelector('.btn-sucesso-footer');
    btnSucesso.innerHTML = '<i class="fas fa-save"></i> FINALIZAR E ADICIONAR DOCUMENTO';
    btnSucesso.style.background = '#1abc9c';

    aplicarDadosPadrao();
    document.getElementById('nome_aluno').focus();
}

/**
 * Modo Edição: Carrega dados no formulário
 */
async function abrirEdicaoDoc(idCard) {
    try {
        const res = await chamarApi(`/documento/detalhes/${idCard}`);
        if (res.erro) { alert(res.message); return; }

        const doc = res.dados;
        modoEdicao = true;
        idCardEdicao = idCard;

        // Preenche campos
        document.getElementById('nome_aluno').value = doc.NomeDocumento;
        document.getElementById('nome_escola_fixo').value = doc.InsEnsinoDocumento;
        document.getElementById('curso_aluno').value = doc.serieDocumento;
        document.getElementById('data_nascimento').value = doc.dataNascDocumento;
        document.getElementById('cpf_aluno').value = doc.nCPF;
        document.getElementById('rg_aluno').value = doc.nRGDocumento;

        // Mostra a foto atual
        document.getElementById('wrapper-crop').style.display = 'none';
        document.getElementById('preview-final').style.display = 'block';
        document.getElementById('foto_cortada_resultado').src = `../../${doc.fotoDocumento}`;
        fotoFinalBase64 = ""; 

        // Altera UI
        document.querySelector('.top-bar h1').innerHTML = `<i class="fas fa-edit"></i> Editando Documento: ${idCard}`;
        const btnSucesso = document.querySelector('.btn-sucesso-footer');
        btnSucesso.innerHTML = '<i class="fas fa-sync-alt"></i> SALVAR ALTERAÇÕES';
        btnSucesso.style.background = '#f6ad55';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (e) { console.error(e); }
}

/**
 * Lógica do Cropper
 */
function iniciarCrop(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const image = document.getElementById('image-to-crop');
        image.src = e.target.result;
        document.getElementById('wrapper-crop').style.display = 'block';
        document.getElementById('preview-final').style.display = 'none';
        if (cropper) cropper.destroy();
        cropper = new Cropper(image, { aspectRatio: 3/4, viewMode: 1 });
    };
    reader.readAsDataURL(file);
}

function cortarFoto() {
    if (!cropper) return;
    const canvas = cropper.getCroppedCanvas({ width: 300, height: 400 });
    fotoFinalBase64 = canvas.toDataURL('image/jpeg', 0.9);
    document.getElementById('foto_cortada_resultado').src = fotoFinalBase64;
    document.getElementById('wrapper-crop').style.display = 'none';
    document.getElementById('preview-final').style.display = 'block';
}

/**
 * Suspender Documento (idStatus = 4)
 */
async function excluirDoc(idCard) {
    if (!confirm(`Deseja suspender o documento ${idCard}?`)) return;
    try {
        const res = await chamarApi(`/documento/suspender/${idCard}`, 'POST');
        if (!res.erro) { listarDocumentosCriados(); }
        else { alert(res.message); }
    } catch (e) { console.error(e); }
}

/**
 * Salvar: Pode ser Novo Registro ou Atualização
 */
async function adicionarDocumento() {
    const btn = document.querySelector('.btn-sucesso-footer');
    const user = obterUsuario();

    // Se for NOVO, a foto é obrigatória. Se for EDIÇÃO, pode manter a antiga.
    if(!modoEdicao && !fotoFinalBase64) { alert("⚠️ Selecione e corte a foto."); return; }

    const dados = {
        idInstituicao: user.idInstituicao,
        idUsuario: user.id,
        nome: document.getElementById('nome_aluno').value.toUpperCase().trim(),
        escola: document.getElementById('nome_escola_fixo').value.trim(),
        curso: document.getElementById('curso_aluno').value.trim(),
        nascimento: document.getElementById('data_nascimento').value,
        cpf: document.getElementById('cpf_aluno').value.trim(),
        rg: document.getElementById('rg_aluno').value.trim(),
        foto: fotoFinalBase64 // Se vazio na edição, o PHP mantém a antiga
    };

    if (!dados.nome || !dados.escola || !dados.curso || !dados.nascimento || !dados.cpf || !dados.rg) {
        alert("⚠️ Preencha todos os campos obrigatórios."); return;
    }

    const endpoint = modoEdicao ? `/documento/atualizar/${idCardEdicao}` : '/documento/registrar';
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> SALVANDO...';

    try {
        const res = await chamarApi(endpoint, 'POST', dados);
        if (!res.erro) {
            alert(modoEdicao ? "✅ Atualizado!" : "✅ Criado!");
            limparCamposAluno();
            listarDocumentosCriados();
        } else { alert(res.message); }
    } catch (e) { alert("🚫 Erro de conexão."); }
    
    btn.disabled = false;
    btn.innerHTML = modoEdicao ? '<i class="fas fa-sync-alt"></i> SALVAR ALTERAÇÕES' : '<i class="fas fa-save"></i> FINALIZAR E ADICIONAR DOCUMENTO';
}

document.addEventListener('DOMContentLoaded', prepararPagina);