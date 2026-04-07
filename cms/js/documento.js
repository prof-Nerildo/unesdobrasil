/**
 * Lógica de Criação de Documento Estudantil - UNES 2.0
 * Desenvolvido para cadastro em massa com reset inteligente.
 */

let cropper;
let fotoFinalBase64 = "";

// MEMÓRIA DE CURTO PRAZO: Guarda as regras da instituição para o "Reset de Fábrica"
let dadosOriginaisInst = {
    nome: "",
    curso: "",
    podeEditarInst: "nao",
    podeEditarCurso: "nao"
};

/**
 * 1. PREPARAÇÃO DA PÁGINA (Executa ao carregar)
 */
async function prepararPagina() {
    const user = obterUsuario(); // Função do seu api.js
    if (!user) {
        window.location.href = '../login.html';
        return;
    }

    const id = user.idInstituicao;
    try {
        const res = await chamarApi(`/instituicao/detalhes/${id}`);
        const inst = res.dados;

        // Validação de Status da Instituição
        if (!inst || inst.idStatus != 2) {
            document.getElementById('container-principal').innerHTML = `
                <div style="text-align:center; padding: 80px;">
                    <i class="fas fa-lock" style="font-size: 50px; color: #e74c3c; margin-bottom: 20px;"></i>
                    <h2>Acesso Suspenso</h2>
                    <p>Esta instituição não tem permissão para emitir documentos.</p>
                </div>`;
            return;
        }

        // SALVA NA MEMÓRIA GLOBAL: Guardamos o que veio do banco
        dadosOriginaisInst.nome = inst.label_edita_instituicao || inst.nome_fantasia;
        dadosOriginaisInst.curso = inst.label_edita_curso || "";
        dadosOriginaisInst.podeEditarInst = inst.pode_editar_instituicao;
        dadosOriginaisInst.podeEditarCurso = inst.pode_editar_curso;

        // Aplica os dados nos campos pela primeira vez
        aplicarDadosPadrao();

    } catch (error) {
        console.error("Erro ao carregar dados da instituição:", error);
    }
}

/**
 * APLICA DADOS PADRÃO: Restaura os campos de Escola e Curso conforme as regras
 */
function aplicarDadosPadrao() {
    const campoInst = document.getElementById('nome_escola_fixo');
    const campoCurso = document.getElementById('curso_aluno');

    // Regra da Instituição
    if (dadosOriginaisInst.podeEditarInst === 'sim') {
        campoInst.value = ""; 
        campoInst.readOnly = false;
        campoInst.style.background = "#fff";
        campoInst.placeholder = "Digite o nome da Instituição";
    } else {
        campoInst.value = dadosOriginaisInst.nome;
        campoInst.readOnly = true;
        campoInst.style.background = "#f8f9fa";
    }

    // Regra do Curso
    if (dadosOriginaisInst.podeEditarCurso === 'sim') {
        campoCurso.value = ""; 
        campoCurso.readOnly = false;
        campoCurso.style.background = "#fff";
        campoCurso.placeholder = "Ex: 3º Ano Ensino Médio";
    } else {
        campoCurso.value = dadosOriginaisInst.curso;
        campoCurso.readOnly = true;
        campoCurso.style.background = "#f8f9fa";
    }
}

/**
 * LIMPEZA INTELIGENTE: Limpa o aluno, mata o cropper e restaura a escola
 */
function limparCamposAluno() {
    // 1. Limpa Dados do Aluno
    document.getElementById('nome_aluno').value = "";
    document.getElementById('data_nascimento').value = "";
    document.getElementById('cpf_aluno').value = "";
    document.getElementById('rg_aluno').value = "";
    
    // 2. Mata o Cropper e a Foto
    fotoFinalBase64 = "";
    document.getElementById('preview-final').style.display = 'none';
    document.getElementById('wrapper-crop').style.display = 'none';
    document.getElementById('input_foto').value = "";
    if (cropper) cropper.destroy();

    // 3. RESTAURA ESCOLA/CURSO (Pega da memória global)
    aplicarDadosPadrao();

    // 4. Foco no nome para o próximo cadastro
    document.getElementById('nome_aluno').focus();
}

/**
 * 2. INICIA O CROPPER (Upload de Foto)
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
        cropper = new Cropper(image, { aspectRatio: 3 / 4, viewMode: 1 });
    };
    reader.readAsDataURL(file);
}

/**
 * 3. CONFIRMA O CORTE
 */
function cortarFoto() {
    if (!cropper) return;
    const canvas = cropper.getCroppedCanvas({ width: 300, height: 400 });
    fotoFinalBase64 = canvas.toDataURL('image/jpeg', 0.9);
    document.getElementById('foto_cortada_resultado').src = fotoFinalBase64;
    document.getElementById('wrapper-crop').style.display = 'none';
    document.getElementById('preview-final').style.display = 'block';
}

/**
 * 4. FINALIZAR E SALVAR (Envio para a API)
 */
async function adicionarDocumento() {
    const btn = document.querySelector('.btn-sucesso-footer');
    
    // Coleta dos dados
    const user = obterUsuario();
    const payload = {
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

    // VALIDAÇÃO DE CAMPOS OBRIGATÓRIOS
    if (!payload.nome || !payload.escola || !payload.curso || !payload.nascimento || !payload.cpf || !payload.rg || !payload.foto) {
        alert("⚠️ Todos os campos são obrigatórios. Verifique se a foto foi cortada.");
        return;
    }

    // UI: Feedback visual
    btn.disabled = true;
    const textoOriginal = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> PROCESSANDO...';

    try {
        const result = await chamarApi('/documento/registrar', 'POST', payload);

        if (!result.erro) {
            alert("✅ Sucesso! Carteirinha Gerada: " + result.id);
            
            // EXECUTA O RESET INTELIGENTE
            limparCamposAluno();
            
            // Destrava o botão para o próximo
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        } else {
            alert("❌ Erro: " + result.message);
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        }
    } catch (error) {
        alert("🚫 Erro na conexão com o servidor. Verifique o console.");
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    }
}

// Inicializa a página ao carregar o DOM
document.addEventListener('DOMContentLoaded', prepararPagina);