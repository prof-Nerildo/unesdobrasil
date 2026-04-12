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
                        <input type="text" id="cnpj" readonly style="background: #f8f9fa;">
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

            <div class="form-section">
                <h3><i class="fas fa-map-marker-alt"></i> Localização</h3>
                <div class="grid-3 mb-3">
                    <div class="field">
                        <label>CEP</label>
                        <input type="text" id="cep">
                    </div>
                    <div class="field" style="grid-column: span 2;">
                        <label>Logradouro</label>
                        <input type="text" id="logradouro">
                    </div>
                </div>
                <div class="grid-4 mb-3">
                    <div class="field">
                        <label>Número</label>
                        <input type="text" id="numero">
                    </div>
                    <div class="field">
                        <label>Bairro</label>
                        <input type="text" id="bairro">
                    </div>
                    <div class="field">
                        <label>Cidade</label>
                        <input type="text" id="cidade">
                    </div>
                    <div class="field">
                        <label>UF</label>
                        <input type="text" id="uf" maxlength="2">
                    </div>
                </div>
                <div class="field">
                    <label>Complemento</label>
                    <input type="text" id="complemento">
                </div>
            </div>

            <hr class="divider">

            <div class="form-section">
                <h3><i class="fas fa-phone"></i> Contatos</h3>
                <div class="grid-2">
                    <div class="field">
                       <label>E-mail de Contato (Secretaria)</label>
                        <input type="email" id="email_contato"> 
                    </div>
                    <div class="field">
                        <label>Telefone Principal</label>
                        <input type="text" id="telefone" placeholder="(00) 0000-0000">
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
    .field { display: flex; flex-direction: column; gap: 8px; }
    .field label { font-weight: 600; color: #555; font-size: 13px; }
    .field input { padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; outline: none; transition: 0.3s; }
    .field input:focus { border-color: #f39c12; }
    .divider { margin: 30px 0; border: 0; border-top: 1px solid #eee; }
    .btn-sucesso { background: #27ae60; border: none; padding: 12px 30px; border-radius: 6px; cursor: pointer; color: #fff; font-weight: bold; transition: 0.3s; display: flex; align-items: center; gap: 10px; }
    .btn-sucesso:hover { background: #219150; transform: translateY(-2px); }
    .form-actions { display: flex; justify-content: flex-end; }
</style>

<script>
    async function carregarDadosInstituicao() {
    // Na instituição, pegamos o ID do objeto user_unes, não do edit_id
    const session = JSON.parse(localStorage.getItem('user_unes'));
    const id = session.idInstituicao;

    if (!id) return;

    try {
        const res = await chamarApi(`/api/instituicao/detalhes/${id}`);
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

            // CONTATOS - Agora com o ID correto 'email_contato'
            document.getElementById('email_contato').value = inst.email_contato || '';
            document.getElementById('telefone').value = inst.telefone || '';
        }
    } catch (error) {
        console.error("Erro na carga da Instituição:", error);
    }
}

async function salvarAlteracoes() {
    const session = JSON.parse(localStorage.getItem('user_unes'));
    const id = session.idInstituicao;
    
    // Pegamos os valores um por um para garantir que nada vá nulo
    const dados = {
        razao_social: document.getElementById('razao_social').value,
        nome_fantasia: document.getElementById('nome_fantasia').value,
        
        // FORÇANDO A LEITURA DO CNPJ (Mesmo sendo readonly)
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
    };

    // DEBUG: Antes de enviar, veja no console se o CNPJ aparece
    console.log("Dados que serão enviados:", dados);

    if (!dados.cnpj) {
        alert("Erro: O CNPJ não foi carregado corretamente.");
        return;
    }

    try {
        const res = await chamarApi(`/api/instituicao/perfil-atualizar/${id}`, 'PUT', dados);
        if(!res.erro) {
            alert("✅ Dados atualizados com sucesso!");
        } else {
            alert("❌ Erro ao atualizar: " + res.message);
        }
    } catch (error) {
        alert("Erro na comunicação com o servidor.");
    }
}

// Inicializa
document.addEventListener('DOMContentLoaded', carregarDadosInstituicao);
</script>

<?php include_once '../includes/footerUnes-2.php'; ?>