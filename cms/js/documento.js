let cropper;
let fotoFinalBase64 = "";
let todosDocumentos = [];
let paginaAtual = 1;
const itensPorPagina = 10;
let dadosOriginaisInst = { nome: "", curso: "", podeEditarInst: "nao", podeEditarCurso: "nao" };

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

async function listarDocumentosCriados() {
    const user = obterUsuario();
    try {
        const res = await chamarApi(`/documento/listar-criados/${user.idInstituicao}`);
        if (!res.erro) { todosDocumentos = res.dados; renderizarTabela(); }
    } catch (e) { console.error(e); }
}

function renderizarTabela() {
    const corpo = document.getElementById('tabela_docs_corpo');
    const busca = document.getElementById('busca_documento').value.toLowerCase();
    
    let filtrados = todosDocumentos.filter(d => 
        d.NomeDocumento.toLowerCase().includes(busca) || 
        d.nCPF.includes(busca) || d.idCard.includes(busca)
    );

    const totalPaginas = Math.ceil(filtrados.length / itensPorPagina);
    const inicio = (paginaAtual - 1) * itensPorPagina;
    const listaExibir = filtrados.slice(inicio, inicio + itensPorPagina);

    let html = "";
    listaExibir.forEach(d => {
        const dNasc = d.dataNascDocumento.split('-').reverse().join('/');
        const dCriacao = d.dataCriacao.split(' ')[0].split('-').reverse().join('/');
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
                <td>
                    <button class="btn-edit-table" onclick="abrirEdicaoDoc('${d.idCard}')"><i class="far fa-edit"></i></button>
                    <button class="btn-delete-table" onclick="excluirDoc('${d.idCard}')"><i class="far fa-trash-alt"></i></button>
                </td>
            </tr>`;
    });
    corpo.innerHTML = html || '<tr><td colspan="10" class="text-center">Nenhum registro encontrado.</td></tr>';
    renderPaginacao(totalPaginas);
}

function renderPaginacao(total) {
    const container = document.getElementById('paginacao_docs');
    let html = "";
    if (total <= 1) { container.innerHTML = ""; return; }
    for (let i = 1; i <= total; i++) {
        html += `<button class="btn-page ${i === paginaAtual ? 'active' : ''}" onclick="mudarPagina(${i})">${i}</button>`;
    }
    container.innerHTML = html;
}

function mudarPagina(p) { paginaAtual = p; renderizarTabela(); }
function filtrarDocumentos() { paginaAtual = 1; renderizarTabela(); }

function limparCamposAluno() {
    ['nome_aluno', 'data_nascimento', 'cpf_aluno', 'rg_aluno'].forEach(id => document.getElementById(id).value = "");
    fotoFinalBase64 = "";
    document.getElementById('preview-final').style.display = 'none';
    document.getElementById('wrapper-crop').style.display = 'none';
    document.getElementById('input_foto').value = "";
    if (cropper) cropper.destroy();
    aplicarDadosPadrao();
    document.getElementById('nome_aluno').focus();
}

function iniciarCrop(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const image = document.getElementById('image-to-crop');
        image.src = e.target.result;
        
        // Exibe o wrapper ANTES de iniciar o Cropper
        document.getElementById('wrapper-crop').style.display = 'block';
        document.getElementById('preview-final').style.display = 'none';

        if (cropper) cropper.destroy();
        
        // Inicializa o Cropper com a imagem carregada
        cropper = new Cropper(image, { 
            aspectRatio: 3 / 4, 
            viewMode: 1,
            autoCropArea: 1,
            responsive: true,
            restore: false
        });
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

async function excluirDoc(idCard) {
    // Usando um confirm simples ou SweetAlert se você tiver
    const confirmar = confirm(`Deseja realmente suspender o documento ${idCard}? Ele sairá da lista de ativos.`);
    
    if (!confirmar) return;

    try {
        const res = await chamarApi(`/documento/suspender/${idCard}`, 'POST');
        
        if (!res.erro) {
            alert("✅ Documento suspenso com sucesso!");
            // RECARREGA A TABELA (Sincronizado com a paginação)
            listarDocumentosCriados(); 
        } else {
            alert("❌ Erro: " + res.message);
        }
    } catch (error) {
        console.error("Erro ao suspender:", error);
        alert("🚫 Falha na comunicação com o servidor.");
    }
}


async function adicionarDocumento() {
    const btn = document.querySelector('.btn-sucesso-footer');
    if(!fotoFinalBase64) { alert("⚠️ Corte a foto do aluno."); return; }
    const user = obterUsuario();
    const dados = {
        idInstituicao: user.idInstituicao,
        idUsuario: user.id,
        nome: document.getElementById('nome_aluno').value.toUpperCase().trim(),
        escola: document.getElementById('nome_escola_fixo').value.trim(),
        curso: document.getElementById('curso_aluno').value.trim(),
        nascimento: document.getElementById('data_nascimento').value,
        cpf: document.getElementById('cpf_aluno').value.trim(),
        rg: document.getElementById('rg_aluno').value.trim(),
        foto: fotoFinalBase64
    };
    if (!dados.nome || !dados.escola || !dados.curso || !dados.nascimento || !dados.cpf || !dados.rg) {
        alert("⚠️ Todos os campos são obrigatórios."); return;
    }
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> PROCESSANDO...';
    try {
        const res = await chamarApi('/documento/registrar', 'POST', dados);
        if (!res.erro) {
            alert("✅ Sucesso! ID: " + res.id);
            limparCamposAluno();
            listarDocumentosCriados();
        } else { alert("❌ " + res.message); }
    } catch (e) { alert("🚫 Erro na conexão."); }
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save"></i> FINALIZAR E ADICIONAR DOCUMENTO';
}

document.addEventListener('DOMContentLoaded', prepararPagina);