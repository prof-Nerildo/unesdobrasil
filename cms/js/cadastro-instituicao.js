// 1. Gerencia o bloqueio e preenchimento automático dos campos
function gerenciarCamposEditaveis() {
    // --- LÓGICA PARA INSTITUIÇÃO ---
    const podeEditInst = document.getElementById('pode_editar_instituicao').value;
    const campoLabelInst = document.getElementById('label_edita_instituicao');

    if (podeEditInst === 'nao') {
        // NÃO pode editar (pelo cliente), então VOCÊ define o nome agora
        campoLabelInst.disabled = false; 
        campoLabelInst.style.backgroundColor = "#fff";
        if (campoLabelInst.value === "") campoLabelInst.value = "Instituição Ensino";
    } else {
        // SIM, o cliente pode editar, então o campo fica desabilitado aqui
        campoLabelInst.disabled = true;
        campoLabelInst.style.backgroundColor = "#f5f5f5";
        campoLabelInst.value = ""; 
    }

    // --- LÓGICA PARA CURSO ---
    const podeEditCurso = document.getElementById('pode_editar_curso').value;
    const campoLabelCurso = document.getElementById('label_edita_curso');

    if (podeEditCurso === 'nao') {
        campoLabelCurso.disabled = false;
        campoLabelCurso.style.backgroundColor = "#fff";
        if (campoLabelCurso.value === "") campoLabelCurso.value = "Série / Curso";
    } else {
        campoLabelCurso.disabled = true;
        campoLabelCurso.style.backgroundColor = "#f5f5f5";
        campoLabelCurso.value = ""; 
    }

    // --- LÓGICA PARA CATRACA ---
    const usaCatraca = document.getElementById('usa_catraca').value;
    const campoModelo = document.getElementById('modelo_catraca');
    const campoQtd = document.getElementById('quantidade_catraca');

    if (usaCatraca === 'nao') {
        campoModelo.value = "";
        campoModelo.disabled = true;
        campoModelo.style.backgroundColor = "#f5f5f5";
        campoQtd.value = 0;
        campoQtd.disabled = true;
        campoQtd.style.backgroundColor = "#f5f5f5";
    } else {
        campoModelo.disabled = false;
        campoModelo.style.backgroundColor = "#fff";
        campoQtd.disabled = false;
        campoQtd.style.backgroundColor = "#fff";
    }
}

// 2. Carrega os dados do Banco para o Formulário
async function inicializarEdicao() {
    const id = localStorage.getItem('edit_id_instituicao');
    if (!id) return;

    try {
        const res = await chamarApi(`/api/instituicao/buscar/${id}`);
        const inst = res.dados;

        if (inst) {
            // DADOS BÁSICOS
            document.getElementById('razao_social').value = inst.razao_social || '';
            document.getElementById('nome_fantasia').value = inst.nome_fantasia || '';
            document.getElementById('cnpj').value = inst.cnpj || '';
            document.getElementById('insc_estadual').value = inst.insc_estadual || '';
            document.getElementById('insc_municipal').value = inst.insc_municipal || '';
            
            // LOCALIZAÇÃO (Onde estava o erro de apagar)
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

            // PERSONALIZAÇÃO
            document.getElementById('pode_editar_instituicao').value = inst.pode_editar_instituicao || 'nao';
            document.getElementById('label_edita_instituicao').value = inst.label_edita_instituicao || '';
            document.getElementById('pode_editar_curso').value = inst.pode_editar_curso || 'nao';
            document.getElementById('label_edita_curso').value = inst.label_edita_curso || '';

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

// 3. Salva as Alterações (Enviando TODOS os campos para não apagar o endereço)
async function salvarAlteracoes() {
    const id = localStorage.getItem('edit_id_instituicao');
    
    // Montamos o objeto com TODOS os campos do formulário
    const dados = {
        razao_social: document.getElementById('razao_social').value,
        nome_fantasia: document.getElementById('nome_fantasia').value,
        cnpj: document.getElementById('cnpj').value,
        insc_estadual: document.getElementById('insc_estadual').value,
        insc_municipal: document.getElementById('insc_municipal').value,
        
        // Endereço (Essencial para não apagar no banco)
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

    try {
        const res = await chamarApi(`/api/instituicao/alterar/${id}`, 'PUT', dados);
        if(!res.erro) {
            alert("Instituição Atualizada com Sucesso!");
            window.location.href = 'instituicoes.php';
        } else {
            alert("Erro ao salvar: " + res.message);
        }
    } catch (error) {
        alert("Erro na comunicação com o servidor.");
    }
}

// 4. Configuração dos Eventos
document.getElementById('pode_editar_instituicao').addEventListener('change', gerenciarCamposEditaveis);
document.getElementById('pode_editar_curso').addEventListener('change', gerenciarCamposEditaveis);
document.getElementById('usa_catraca').addEventListener('change', gerenciarCamposEditaveis);

// Inicializa a página
inicializarEdicao();