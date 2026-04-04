// Função para bloquear campos se não puder editar
function gerenciarCamposEditaveis() {
    const campos = [
        { select: 'pode_editar_instituicao', input: 'label_edita_instituicao', padrao: 'Instituição Ensino' },
        { select: 'pode_editar_curso', input: 'label_edita_curso', padrao: 'Série / Curso' }
    ];

    campos.forEach(item => {
        const select = document.getElementById(item.select);
        const input = document.getElementById(item.input);

        if (select && input) {
            if (select.value === 'nao') {
                input.value = item.padrao;
                input.readOnly = true;
                input.style.backgroundColor = "#f5f5f5";
            } else {
                input.readOnly = false;
                input.style.backgroundColor = "#fff";
            }
        }
    });
}

// Função Principal de Carga
async function inicializarEdicao() {
    const id = localStorage.getItem('edit_id_instituicao');
    if (!id) return;

    try {
        const res = await chamarApi(`/api/instituicao/buscar/${id}`); // Ajuste o caminho se necessário
        const inst = res.dados;

        if (inst) {
            // DADOS BÁSICOS
            document.getElementById('razao_social').value = inst.razao_social || '';
            document.getElementById('nome_fantasia').value = inst.nome_fantasia || '';
            document.getElementById('cnpj').value = inst.cnpj || '';
            document.getElementById('insc_estadual').value = inst.insc_estadual || '';
            document.getElementById('insc_municipal').value = inst.insc_municipal || '';
            
            // LOCALIZAÇÃO
            document.getElementById('cep').value = inst.cep || '';
            document.getElementById('logradouro').value = inst.logradouro || '';
            document.getElementById('numero').value = inst.numero || '';
            document.getElementById('bairro').value = inst.bairro || ''; 
            document.getElementById('cidade').value = inst.cidade || '';
            document.getElementById('uf').value = inst.uf || '';
            document.getElementById('complemento').value = inst.complemento || '';

            // CONTATOS
            document.getElementById('email_contato').value = inst.email_contato || '';
            document.getElementById('telefone').value = inst.telefone || '';

            // TERMOS PERSONALIZADOS
            document.getElementById('pode_editar_instituicao').value = inst.pode_editar_instituicao || 'nao';
            document.getElementById('label_edita_instituicao').value = inst.label_edita_instituicao || 'Instituição Ensino';
            document.getElementById('pode_editar_curso').value = inst.pode_editar_curso || 'nao';
            document.getElementById('label_edita_curso').value = inst.label_edita_curso || 'Série / Curso';

            // CATRACA
            document.getElementById('usa_catraca').value = inst.usa_catraca || 'nao';
            document.getElementById('modelo_catraca').value = inst.modelo || '';
            document.getElementById('quantidade_catraca').value = inst.quantidade || 0;

            // FINANCEIRO
            document.getElementById('valor_documento_nacional').value = inst.valor_documento_nacional || '0.00';
            document.getElementById('valor_frete').value = inst.valor_frete || '0.00';

            gerenciarCamposEditaveis();
        }
    } catch (error) {
        console.error("Erro na carga:", error);
    }
}

// Ouvintes de eventos
document.getElementById('pode_editar_instituicao').addEventListener('change', gerenciarCamposEditaveis);
document.getElementById('pode_editar_curso').addEventListener('change', gerenciarCamposEditaveis);

// Função para SALVAR
async function salvarAlteracoes() {
    const id = localStorage.getItem('edit_id_instituicao');
    const dados = {
        razao_social: document.getElementById('razao_social').value,
        nome_fantasia: document.getElementById('nome_fantasia').value,
        cnpj: document.getElementById('cnpj').value,
        insc_estadual: document.getElementById('insc_estadual').value,
        insc_municipal: document.getElementById('insc_municipal').value,
        cep: document.getElementById('cep').value,
        logradouro: document.getElementById('logradouro').value,
        numero: document.getElementById('numero').value,
        bairro: document.getElementById('bairro').value,
        cidade: document.getElementById('cidade').value,
        uf: document.getElementById('uf').value,
        complemento: document.getElementById('complemento').value,
        email_contato: document.getElementById('email_contato').value,
        telefone: document.getElementById('telefone').value,
        pode_editar_instituicao: document.getElementById('pode_editar_instituicao').value,
        label_edita_instituicao: document.getElementById('label_edita_instituicao').value,
        pode_editar_curso: document.getElementById('pode_editar_curso').value,
        label_edita_curso: document.getElementById('label_edita_curso').value,
        usa_catraca: document.getElementById('usa_catraca').value,
        modelo_catraca: document.getElementById('modelo_catraca').value,
        quantidade_catraca: document.getElementById('quantidade_catraca').value,
        valor_documento_nacional: document.getElementById('valor_documento_nacional').value,
        valor_frete: document.getElementById('valor_frete').value
    };

    const res = await chamarApi(`/api/instituicao/alterar/${id}`, 'PUT', dados);
    if(!res.erro) {
        alert("Instituição Validada e Ativada!");
        window.location.href = 'instituicoes.php';
    } else {
        alert("Erro: " + res.message);
    }
}

inicializarEdicao();