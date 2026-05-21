<?php include_once '../includes/headerInstituicao.php'; ?>
<?php include_once '../includes/sidebarInstituicao.php'; ?>

<main class="content">
    <section class="main-section">
        <header class="top-bar">
            <h1><i class="fas fa-edit"></i> Dados da Instituição</h1>
            <button class="btn-sucesso" onclick="salvarAlteracoes()">
                <i class="fas fa-save"></i> SALVAR ALTERAÇÕES
            </button>
        </header>

        <div class="card-form">

            <!-- ===== DADOS CADASTRAIS ===== -->
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Dados Cadastrais</h3>
                <div class="grid-2 mb-3">
                    <div class="field">
                        <label>Razão Social</label>
                        <input type="text" id="razao_social">
                    </div>
                    <div class="field">
                        <label>Nome Fantasia</label>
                        <input type="text" id="nome_fantasia">
                    </div>
                </div>
                <div class="grid-3">
                    <div class="field">
                        <label>CNPJ / CPF (Leitura)</label>
                        <input type="text" id="cnpj" readonly class="readonly-field">
                    </div>
                    <div class="field">
                        <label>Insc. Estadual</label>
                        <input type="text" id="insc_estadual">
                    </div>
                    <div class="field">
                        <label>Insc. Municipal</label>
                        <input type="text" id="insc_municipal">
                    </div>
                </div>
            </div>

            <hr class="divider">

            <!-- ===== LOCALIZAÇÃO ===== -->
            <div class="form-section">
                <h3><i class="fas fa-map-marker-alt"></i> Localização</h3>
                <div class="grid-4 mb-3">
                    <div class="field">
                        <label>CEP</label>
                        <div class="cep-wrap">
                            <input type="text" id="cep" placeholder="00000-000" maxlength="9"
                                   oninput="mascaraCep(this)" onblur="buscarCep()">
                            <div class="cep-spinner" id="cep-spinner"></div>
                        </div>
                        <span class="field-feedback" id="fb-cep"></span>
                    </div>
                    <div class="field" style="grid-column: span 3;">
                        <label>Logradouro</label>
                        <input type="text" id="logradouro" readonly class="readonly-field"
                               placeholder="Preenchido automaticamente pelo CEP">
                    </div>
                </div>
                <div class="grid-4 mb-3">
                    <div class="field">
                        <label>Número <span class="req">*</span></label>
                        <input type="text" id="numero" placeholder="Ex: 123">
                    </div>
                    <div class="field">
                        <label>Bairro</label>
                        <input type="text" id="bairro" readonly class="readonly-field"
                               placeholder="Preenchido pelo CEP">
                    </div>
                    <div class="field">
                        <label>Cidade</label>
                        <input type="text" id="cidade" readonly class="readonly-field"
                               placeholder="Preenchida pelo CEP">
                    </div>
                    <div class="field">
                        <label>UF</label>
                        <input type="text" id="uf" maxlength="2" readonly class="readonly-field">
                    </div>
                </div>
                <div class="field">
                    <label>Complemento</label>
                    <input type="text" id="complemento" placeholder="Sala, Bloco, Apto... (opcional)">
                </div>
            </div>

            <hr class="divider">

            <!-- ===== CONTATOS ===== -->
            <div class="form-section">
                <h3><i class="fas fa-phone"></i> Contatos</h3>
                <div class="grid-2">
                    <div class="field">
                        <label>E-mail de Contato (Secretaria) <span class="req">*</span></label>
                        <input type="email" id="email_contato" placeholder="secretaria@escola.com.br">
                    </div>
                    <div class="field">
                        <label>Telefone Principal <span class="req">*</span></label>
                        <input type="text" id="telefone" placeholder="(00) 00000-0000" maxlength="15"
                               oninput="mascaraTelefone(this)">
                    </div>
                </div>
            </div>

            <hr class="divider">
            <div class="form-actions mt-4">
                <button class="btn-sucesso" onclick="salvarAlteracoes()">
                    <i class="fas fa-save"></i> SALVAR ALTERAÇÕES
                </button>
            </div>
        </div>
    </section>
</main>

<style>
    .card-form { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin: 20px; }
    .form-section h3 { color: #2c3e50; font-size: 18px; margin-bottom: 20px; border-left: 4px solid #f39c12; padding-left: 10px; }
    .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
    .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
    .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .mb-3  { margin-bottom: 20px; }
    .field { display: flex; flex-direction: column; gap: 6px; }
    .field label { font-weight: 600; color: #555; font-size: 13px; }
    .field input {
        padding: 12px; border: 1px solid #ddd; border-radius: 6px;
        font-size: 14px; outline: none; transition: 0.3s; box-sizing: border-box;
        width: 100%;
    }
    .field input:focus { border-color: #f39c12; box-shadow: 0 0 0 3px rgba(243,156,18,0.12); }

    /* Campos preenchidos pelo ViaCEP — só leitura */
    .readonly-field { background: #f8f9fa !important; color: #6c757d; cursor: default; }
    .readonly-field:focus { border-color: #ddd !important; box-shadow: none !important; }

    /* Spinner do CEP */
    .cep-wrap { position: relative; }
    .cep-spinner {
        position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        width: 16px; height: 16px; border: 2px solid #f39c12;
        border-top-color: transparent; border-radius: 50%;
        animation: spin 0.7s linear infinite; display: none;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Feedback inline */
    .field-feedback { font-size: 12px; min-height: 16px; }
    .field-feedback.ok   { color: #198754; }
    .field-feedback.erro { color: #dc3545; }

    .req { color: #dc3545; margin-left: 2px; }
    .divider { margin: 30px 0; border: 0; border-top: 1px solid #eee; }
    .btn-sucesso {
        background: #27ae60; border: none; padding: 12px 30px; border-radius: 6px;
        cursor: pointer; color: #fff; font-weight: bold; transition: 0.3s;
        display: flex; align-items: center; gap: 10px;
    }
    .btn-sucesso:hover { background: #219150; transform: translateY(-2px); }
    .btn-sucesso:disabled { background: #6c757d; cursor: not-allowed; transform: none; }
    .form-actions { display: flex; justify-content: flex-end; }
    .mt-4 { margin-top: 20px; }
</style>

<script>
    // ============================================================
    // MÁSCARAS
    // ============================================================
    function mascaraCep(input) {
        let v = input.value.replace(/\D/g, '').substring(0, 8);
        if (v.length > 5) v = v.replace(/^(\d{5})(\d{0,3})/, '$1-$2');
        input.value = v;
        const fb = document.getElementById('fb-cep');
        fb.className = 'field-feedback';
        fb.textContent = '';
    }

    function mascaraTelefone(input) {
        let v = input.value.replace(/\D/g, '').substring(0, 11);
        if (v.length === 11)      v = v.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        else if (v.length === 10) v = v.replace(/^(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        else if (v.length > 6)    v = v.replace(/^(\d{2})(\d+)/,          '($1) $2');
        else if (v.length > 2)    v = v.replace(/^(\d{2})(\d*)/,          '($1) $2');
        input.value = v;
    }

    // ============================================================
    // BUSCA DE CEP via ViaCEP
    // ============================================================
    async function buscarCep() {
        const cepRaw  = document.getElementById('cep').value.replace(/\D/g, '');
        const fb      = document.getElementById('fb-cep');
        const spinner = document.getElementById('cep-spinner');

        if (cepRaw.length !== 8) {
            if (cepRaw.length > 0) {
                fb.textContent = 'CEP deve ter 8 dígitos.';
                fb.className   = 'field-feedback erro';
            }
            return;
        }

        spinner.style.display = 'block';
        fb.className  = 'field-feedback';
        fb.textContent = '';

        try {
            const res  = await fetch(`https://viacep.com.br/ws/${cepRaw}/json/`);
            const data = await res.json();

            if (data.erro) {
                fb.textContent = 'CEP não encontrado. Verifique e tente novamente.';
                fb.className   = 'field-feedback erro';
                limparEndereco();
            } else {
                document.getElementById('logradouro').value = data.logradouro || '';
                document.getElementById('bairro').value     = data.bairro     || '';
                document.getElementById('cidade').value     = data.localidade || '';
                document.getElementById('uf').value         = data.uf         || '';

                fb.textContent = `✓ ${data.localidade} — ${data.uf}`;
                fb.className   = 'field-feedback ok';

                document.getElementById('numero').focus();
            }
        } catch(e) {
            fb.textContent = 'Erro ao consultar o CEP. Verifique sua conexão.';
            fb.className   = 'field-feedback erro';
        } finally {
            spinner.style.display = 'none';
        }
    }

    function limparEndereco() {
        ['logradouro', 'bairro', 'cidade', 'uf'].forEach(id => document.getElementById(id).value = '');
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('cep').addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); buscarCep(); }
        });
    });

    // ============================================================
    // CARREGAR DADOS DA INSTITUIÇÃO
    // ============================================================
    async function carregarDadosInstituicao() {
        const session = JSON.parse(localStorage.getItem('user_unes'));
        const id = session?.idInstituicao;
        if (!id) return;

        try {
            const res  = await chamarApi(`/api/instituicao/detalhes/${id}`);
            const inst = res.dados;
            if (!inst) return;

            // Dados Cadastrais
            document.getElementById('razao_social').value   = inst.razao_social   || '';
            document.getElementById('nome_fantasia').value  = inst.nome_fantasia  || '';
            document.getElementById('cnpj').value           = inst.cnpj           || '';
            document.getElementById('insc_estadual').value  = inst.insc_estadual  || '';
            document.getElementById('insc_municipal').value = inst.insc_municipal || '';

            // Localização (campos readonly — preenchidos direto ao carregar, sem busca CEP)
            document.getElementById('cep').value         = inst.cep         || '';
            document.getElementById('logradouro').value  = inst.logradouro  || '';
            document.getElementById('numero').value      = inst.numero      || '';
            document.getElementById('bairro').value      = inst.bairro      || '';
            document.getElementById('cidade').value      = inst.cidade      || '';
            document.getElementById('uf').value          = inst.uf          || '';
            document.getElementById('complemento').value = inst.complemento || '';

            // Contatos
            document.getElementById('email_contato').value = inst.email_contato || '';

            // Telefone com máscara
            const telInput = document.getElementById('telefone');
            telInput.value = inst.telefone || '';
            if (telInput.value) mascaraTelefone(telInput);

        } catch (error) {
            console.error('Erro na carga da Instituição:', error);
        }
    }

    // ============================================================
    // SALVAR ALTERAÇÕES
    // ============================================================
    async function salvarAlteracoes() {
        const session = JSON.parse(localStorage.getItem('user_unes'));
        const id = session?.idInstituicao;
        if (!id) { alert('Sessão inválida. Faça login novamente.'); return; }

        const email    = document.getElementById('email_contato').value.trim();
        const telefone = document.getElementById('telefone').value.trim();

        // Validações obrigatórias
        if (!email) {
            alert('⚠ E-mail de contato é obrigatório.');
            document.getElementById('email_contato').focus();
            return;
        }
        if (!telefone || telefone.replace(/\D/g, '').length < 10) {
            alert('⚠ Telefone Principal é obrigatório (mínimo 10 dígitos).');
            document.getElementById('telefone').focus();
            return;
        }

        const dados = {
            razao_social:   document.getElementById('razao_social').value.trim(),
            nome_fantasia:  document.getElementById('nome_fantasia').value.trim(),
            cnpj:           document.getElementById('cnpj').value.trim(),
            insc_estadual:  document.getElementById('insc_estadual').value.trim(),
            insc_municipal: document.getElementById('insc_municipal').value.trim(),
            cep:            document.getElementById('cep').value.trim(),
            logradouro:     document.getElementById('logradouro').value.trim(),
            numero:         document.getElementById('numero').value.trim(),
            complemento:    document.getElementById('complemento').value.trim(),
            bairro:         document.getElementById('bairro').value.trim(),
            cidade:         document.getElementById('cidade').value.trim(),
            uf:             document.getElementById('uf').value.trim(),
            email_contato:  email,
            telefone:       telefone
        };

        // Feedback visual no botão
        const btn = event.currentTarget;
        const textoOriginal = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

        try {
            const res = await chamarApi(`/api/instituicao/perfil-atualizar/${id}`, 'PUT', dados);

            if (!res.erro) {
                btn.innerHTML = '<i class="fas fa-check"></i> Salvo!';
                btn.style.background = '#198754';
                setTimeout(() => {
                    btn.disabled = false;
                    btn.style.background = '';
                    btn.innerHTML = textoOriginal;
                }, 2000);
            } else {
                alert('❌ Erro ao atualizar: ' + res.message);
                btn.disabled = false;
                btn.innerHTML = textoOriginal;
            }
        } catch (error) {
            alert('Erro na comunicação com o servidor.');
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        }
    }

    document.addEventListener('DOMContentLoaded', carregarDadosInstituicao);
</script>

<?php include_once '../includes/footerUnes-2.php'; ?>