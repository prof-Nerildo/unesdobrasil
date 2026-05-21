// ============================================================
// MÁSCARAS
// ============================================================
function mascaraCep(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 8);
    if (v.length > 5) v = v.replace(/^(\d{5})(\d{0,3})/, '$1-$2');
    input.value = v;
    const fb = document.getElementById('fb-cep');
    if (fb) { fb.className = 'field-feedback'; fb.textContent = ''; }
}

function mascaraTelefone(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 11);
    if (v.length === 11)      v = v.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    else if (v.length === 10) v = v.replace(/^(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    else if (v.length > 6)    v = v.replace(/^(\d{2})(\d+)/,          '($1) $2');
    else if (v.length > 2)    v = v.replace(/^(\d{2})(\d*)/,          '($1) $2');
    input.value = v;
}

function mascaraMoeda(input) {
    let digits = input.value.replace(/\D/g, '');
    if (!digits || digits === '0') { input.value = '0,00'; return; }
    // Remove zeros à esquerda
    digits = digits.replace(/^0+/, '') || '0';
    // Garante pelo menos 3 dígitos (centavos)
    while (digits.length < 3) digits = '0' + digits;
    const centavos = digits.slice(-2);
    const reais    = digits.slice(0, -2);
    // Separador de milhar com ponto
    const reaisFormatado = reais.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    input.value = reaisFormatado + ',' + centavos;
}

function desmascaraMoeda(valor) {
    return (valor || '0,00').replace(/\./g, '').replace(',', '.');
}

function aplicarMascaraMoeda(input, valor) {
    // Converte para centavos inteiros, aplica a máscara
    const numero = parseFloat(valor) || 0;
    const centavos = Math.round(numero * 100).toString();
    // Reutiliza mascaraMoeda via um input virtual
    input.value = centavos;
    mascaraMoeda(input);
}

// ============================================================
// BUSCA CEP via ViaCEP
// ============================================================
async function buscarCep() {
    const cepInput = document.getElementById('cep');
    const cepRaw   = cepInput.value.replace(/\D/g, '');
    const fb       = document.getElementById('fb-cep');
    const spinner  = document.getElementById('cep-spinner');

    if (cepRaw.length !== 8) {
        if (cepRaw.length > 0 && fb) {
            fb.textContent = 'CEP deve ter 8 dígitos.';
            fb.className   = 'field-feedback erro';
        }
        return;
    }

    if (spinner) spinner.style.display = 'block';
    if (fb)      { fb.className = 'field-feedback'; fb.textContent = ''; }

    try {
        const res  = await fetch(`https://viacep.com.br/ws/${cepRaw}/json/`);
        const data = await res.json();

        if (data.erro) {
            if (fb) { fb.textContent = 'CEP não encontrado.'; fb.className = 'field-feedback erro'; }
            limparEndereco();
        } else {
            document.getElementById('logradouro').value = data.logradouro || '';
            document.getElementById('bairro').value     = data.bairro     || '';
            document.getElementById('cidade').value     = data.localidade || '';
            document.getElementById('uf').value         = data.uf         || '';

            // Logradouro/bairro/cidade/uf são readonly — travamos edição manual
            setReadonly(['logradouro', 'bairro', 'cidade', 'uf'], true);

            if (fb) { fb.textContent = `✓ ${data.localidade} — ${data.uf}`; fb.className = 'field-feedback ok'; }

            document.getElementById('numero').focus();
        }
    } catch(e) {
        if (fb) { fb.textContent = 'Erro ao consultar o CEP.'; fb.className = 'field-feedback erro'; }
    } finally {
        if (spinner) spinner.style.display = 'none';
    }
}

function limparEndereco() {
    ['logradouro', 'bairro', 'cidade', 'uf'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
}

function setReadonly(ids, state) {
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.readOnly = state;
        el.style.background = state ? '#f8f9fa' : '#fff';
        el.style.color      = state ? '#6c757d' : '';
        el.style.cursor     = state ? 'default'  : '';
    });
}

// ============================================================
// 1. Gerencia campos editáveis (Personalização / Catraca)
// ============================================================
function gerenciarCamposEditaveis() {
    // --- Instituição ---
    const podeEditInst   = document.getElementById('pode_editar_instituicao').value;
    const campoLabelInst = document.getElementById('label_edita_instituicao');
    if (podeEditInst === 'nao') {
        campoLabelInst.disabled = false;
        campoLabelInst.style.backgroundColor = '#fff';
        if (campoLabelInst.value === '') campoLabelInst.value = 'Instituição Ensino';
    } else {
        campoLabelInst.disabled = true;
        campoLabelInst.style.backgroundColor = '#f5f5f5';
        campoLabelInst.value = '';
    }

    // --- Curso ---
    const podeEditCurso   = document.getElementById('pode_editar_curso').value;
    const campoLabelCurso = document.getElementById('label_edita_curso');
    if (podeEditCurso === 'nao') {
        campoLabelCurso.disabled = false;
        campoLabelCurso.style.backgroundColor = '#fff';
        if (campoLabelCurso.value === '') campoLabelCurso.value = 'Série / Curso';
    } else {
        campoLabelCurso.disabled = true;
        campoLabelCurso.style.backgroundColor = '#f5f5f5';
        campoLabelCurso.value = '';
    }

    // --- Catraca ---
    const usaCatraca  = document.getElementById('usa_catraca').value;
    const campoModelo = document.getElementById('modelo_catraca');
    const campoQtd    = document.getElementById('quantidade_catraca');
    if (usaCatraca === 'nao') {
        campoModelo.value = '';
        campoModelo.disabled = true;
        campoModelo.style.backgroundColor = '#f5f5f5';
        campoQtd.value = 0;
        campoQtd.disabled = true;
        campoQtd.style.backgroundColor = '#f5f5f5';
    } else {
        campoModelo.disabled = false;
        campoModelo.style.backgroundColor = '#fff';
        campoQtd.disabled = false;
        campoQtd.style.backgroundColor = '#fff';
    }
}

// ============================================================
// 2. Carrega dados do banco para o formulário
// ============================================================
async function inicializarEdicao() {
    const id = localStorage.getItem('edit_id_instituicao');
    if (!id) return;

    try {
        const res  = await chamarApi(`/api/instituicao/buscar/${id}`);
        const inst = res.dados;
        if (!inst) return;

        // Dados Cadastrais
        document.getElementById('razao_social').value   = inst.razao_social   || '';
        document.getElementById('nome_fantasia').value  = inst.nome_fantasia  || '';
        document.getElementById('cnpj').value           = inst.cnpj           || '';
        document.getElementById('insc_estadual').value  = inst.insc_estadual  || '';
        document.getElementById('insc_municipal').value = inst.insc_municipal || '';

        // Localização — preenche e trava readonly nos campos do ViaCEP
        document.getElementById('cep').value         = inst.cep         || '';
        document.getElementById('logradouro').value  = inst.logradouro  || '';
        document.getElementById('numero').value      = inst.numero      || '';
        document.getElementById('bairro').value      = inst.bairro      || '';
        document.getElementById('cidade').value      = inst.cidade      || '';
        document.getElementById('uf').value          = inst.uf          || '';
        document.getElementById('complemento').value = inst.complemento || '';

        // Trava os campos que o ViaCEP preenche
        setReadonly(['logradouro', 'bairro', 'cidade', 'uf'], true);

        // Contatos
        document.getElementById('email_contato').value = inst.email_contato || '';
        const telInput = document.getElementById('telefone');
        telInput.value = inst.telefone || '';
        if (telInput.value) mascaraTelefone(telInput);

        // Personalização
        document.getElementById('pode_editar_instituicao').value = inst.pode_editar_instituicao || 'nao';
        document.getElementById('label_edita_instituicao').value = inst.label_edita_instituicao || '';
        document.getElementById('pode_editar_curso').value       = inst.pode_editar_curso       || 'nao';
        document.getElementById('label_edita_curso').value       = inst.label_edita_curso       || '';

        // Catraca
        document.getElementById('usa_catraca').value          = inst.usa_catraca || 'nao';
        document.getElementById('modelo_catraca').value       = inst.modelo      || '';
        document.getElementById('quantidade_catraca').value   = inst.quantidade  || 0;

        // Financeiro — aplica máscara de moeda brasileira
        aplicarMascaraMoeda(document.getElementById('valor_documento_nacional'), inst.valor_documento_nacional || 0);
        aplicarMascaraMoeda(document.getElementById('valor_frete'), inst.valor_frete || 0);

        gerenciarCamposEditaveis();

    } catch (error) {
        console.error('Erro na carga:', error);
    }
}

// ============================================================
// 3. Salva as Alterações
// ============================================================
async function salvarAlteracoes() {
    const id = localStorage.getItem('edit_id_instituicao');
    if (!id) { alert('ID de edição não encontrado.'); return; }

    const dados = {
        razao_social:              document.getElementById('razao_social').value.trim(),
        nome_fantasia:             document.getElementById('nome_fantasia').value.trim(),
        cnpj:                      document.getElementById('cnpj').value.trim(),
        insc_estadual:             document.getElementById('insc_estadual').value.trim(),
        insc_municipal:            document.getElementById('insc_municipal').value.trim(),

        cep:                       document.getElementById('cep').value.trim(),
        logradouro:                document.getElementById('logradouro').value.trim(),
        numero:                    document.getElementById('numero').value.trim(),
        bairro:                    document.getElementById('bairro').value.trim(),
        cidade:                    document.getElementById('cidade').value.trim(),
        uf:                        document.getElementById('uf').value.trim(),
        complemento:               document.getElementById('complemento').value.trim(),

        email_contato:             document.getElementById('email_contato').value.trim(),
        telefone:                  document.getElementById('telefone').value.trim(),

        pode_editar_instituicao:   document.getElementById('pode_editar_instituicao').value,
        label_edita_instituicao:   document.getElementById('label_edita_instituicao').value.trim(),
        pode_editar_curso:         document.getElementById('pode_editar_curso').value,
        label_edita_curso:         document.getElementById('label_edita_curso').value.trim(),

        usa_catraca:               document.getElementById('usa_catraca').value,
        modelo_catraca:            document.getElementById('modelo_catraca').value.trim(),
        quantidade_catraca:        document.getElementById('quantidade_catraca').value,

        valor_documento_nacional:  desmascaraMoeda(document.getElementById('valor_documento_nacional').value),
        valor_frete:               desmascaraMoeda(document.getElementById('valor_frete').value)
    };

    const btn = document.querySelector('.btn-sucesso');
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

    try {
        const res = await chamarApi(`/api/instituicao/alterar/${id}`, 'PUT', dados);
        if (!res.erro) {
            btn.innerHTML = '<i class="fas fa-check"></i> Salvo!';
            setTimeout(() => {
                window.location.href = 'instituicoes.php';
            }, 1200);
        } else {
            alert('Erro ao salvar: ' + res.message);
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        }
    } catch (error) {
        alert('Erro na comunicação com o servidor.');
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    }
}

// ============================================================
// 4. Eventos
// ============================================================
document.getElementById('pode_editar_instituicao').addEventListener('change', gerenciarCamposEditaveis);
document.getElementById('pode_editar_curso').addEventListener('change', gerenciarCamposEditaveis);
document.getElementById('usa_catraca').addEventListener('change', gerenciarCamposEditaveis);

// CEP: busca ao sair do campo ou pressionar Enter
const cepEl = document.getElementById('cep');
cepEl.addEventListener('input',   () => mascaraCep(cepEl));
cepEl.addEventListener('blur',    buscarCep);
cepEl.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); buscarCep(); } });

// Telefone: máscara ao digitar
const telEl = document.getElementById('telefone');
telEl.addEventListener('input', () => mascaraTelefone(telEl));

// Moeda BR: máscara ao digitar
const valorDocEl  = document.getElementById('valor_documento_nacional');
const valorFretEl = document.getElementById('valor_frete');
if (valorDocEl)  valorDocEl.addEventListener('input',  () => mascaraMoeda(valorDocEl));
if (valorFretEl) valorFretEl.addEventListener('input', () => mascaraMoeda(valorFretEl));

// Inicializa
inicializarEdicao();