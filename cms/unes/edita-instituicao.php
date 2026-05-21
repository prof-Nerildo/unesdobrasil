<?php include_once '../includes/headerUnes.php'; ?>
<?php include_once '../includes/sidebarUnes.php'; ?>

<main class="content">
    <section class="main-section">
        <header class="top-bar">
            <h1><i class="fas fa-edit"></i> Validar Inst. Ensino</h1>
            <p>Complete os dados para ativar a instituição no sistema.</p>
        </header>

        <div class="card-form">
            
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Dados Cadastrais</h3>
                <div class="grid-2 mb-3">
                    <div class="field">
                        <label>Razão Social</label>
                        <input type="text" id="razao_social" placeholder="Razão Social">
                    </div>
                    <div class="field">
                        <label>Nome Fantasia</label>
                        <input type="text" id="nome_fantasia" placeholder="Nome Fantasia">
                    </div>
                </div>
                <div class="grid-3">
                    <div class="field">
                        <label>CNPJ / CPF</label>
                        <input type="text" id="cnpj" placeholder="00.000.000/0000-00">
                    </div>
                    <div class="field">
                        <label>Insc. Estadual</label>
                        <input type="text" id="insc_estadual" placeholder="Insc. Estadual">
                    </div>
                    <div class="field">
                        <label>Insc. Municipal</label>
                        <input type="text" id="insc_municipal" placeholder="Insc. Municipal">
                    </div>
                </div>
            </div>

            <hr class="divider">

            <div class="form-section">
                <h3><i class="fas fa-map-marker-alt"></i> Localização</h3>
                <div class="grid-3 mb-3">
                    <div class="field">
                        <label>CEP</label>
                        <div class="cep-wrap">
                            <input type="text" id="cep" placeholder="00000-000" maxlength="9">
                            <div class="cep-spinner" id="cep-spinner"></div>
                        </div>
                        <span class="field-feedback" id="fb-cep"></span>
                    </div>
                    <div class="field" style="grid-column: span 2;">
                        <label>Logradouro</label>
                        <input type="text" id="logradouro" placeholder="Preenchido pelo CEP" class="readonly-field" readonly>
                    </div>
                </div>
                <div class="grid-4 mb-3">
                    <div class="field">
                        <label>Número</label>
                        <input type="text" id="numero">
                    </div>
                    <div class="field">
                        <label>Bairro</label>
                        <input type="text" id="bairro" class="readonly-field" readonly placeholder="Pelo CEP">
                    </div>
                    <div class="field">
                        <label>Cidade</label>
                        <input type="text" id="cidade" class="readonly-field" readonly placeholder="Pelo CEP">
                    </div>
                    <div class="field">
                        <label>UF</label>
                        <input type="text" id="uf" maxlength="2" class="readonly-field" readonly>
                    </div>
                </div>
                <div class="field">
                    <label>Complemento</label>
                    <input type="text" id="complemento">
                </div>
            </div>

            <hr class="divider">

            <div class="form-section">
                <h3><i class="fas fa-phone"></i> Contatos da Instituição</h3>
                <div class="grid-2">
                    <div class="field">
                        <label>E-mail de Contato (Secretaria)</label>
                        <input type="email" id="email_contato" placeholder="email@escola.com">
                    </div>
                    <div class="field">
                        <label>Telefone Principal</label>
                        <input type="text" id="telefone" placeholder="(00) 00000-0000" maxlength="15">
                    </div>
                </div>
            </div>

            <hr class="divider">

            <div class="form-section">
                <h3><i class="fas fa-user-edit"></i> Personalização de Termos</h3>
                <div class="grid-2-custom mb-3">
                    <div class="field">
                        <label>Edita Instituição?</label>
                        <select id="pode_editar_instituicao">
                            <option value="nao">Não</option>
                            <option value="sim">Sim</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Instituição Ensino (Label)</label>
                        <input type="text" id="label_edita_instituicao" placeholder="Instituição Ensino">
                    </div>
                </div>
                <div class="grid-2-custom">
                    <div class="field">
                        <label>Edita Curso?</label>
                        <select id="pode_editar_curso">
                            <option value="nao">Não</option>
                            <option value="sim">Sim</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Série / Curso (Label)</label>
                        <input type="text" id="label_edita_curso" placeholder="Série / Curso">
                    </div>
                </div>
            </div>

            <hr class="divider">

            <div class="form-section">
                <h3><i class="fas fa-barcode"></i> Controle de Acesso (Catraca)</h3>
                <div class="grid-3">
                    <div class="field">
                        <label>Possui Catraca?</label>
                        <select id="usa_catraca">
                            <option value="nao">Não</option>
                            <option value="sim">Sim</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Modelo da Catraca</label>
                        <input type="text" id="modelo_catraca" placeholder="Modelo">
                    </div>
                    <div class="field">
                        <label>Quantidade</label>
                        <input type="number" id="quantidade_catraca" value="0">
                    </div>
                </div>
            </div>

            <hr class="divider">

            <div class="form-section">
                <h3><i class="fas fa-dollar-sign"></i> Financeiro</h3>
                <div class="grid-2">
                    <div class="field">
                        <label>Valor Documento Nacional</label>
                        <input type="text" id="valor_documento_nacional" value="0.00">
                    </div>
                    <div class="field">
                        <label>Valor Frete</label>
                        <input type="text" id="valor_frete" value="0.00">
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button class="btn-cancelar" onclick="window.location.href='instituicoes.php'">Cancelar</button>
                <button class="btn-sucesso" onclick="salvarAlteracoes()">
                    <i class="fas fa-check-circle"></i> SALVAR E ATIVAR INSTITUIÇÃO
                </button>
            </div>
        </div>
    </section>

    <?php include_once '../includes/footer.php'; ?>
</main>

<style>
    .card-form { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin: 20px; }
    .form-section h3 { color: #2c3e50; font-size: 18px; margin-bottom: 20px; border-left: 4px solid #f39c12; padding-left: 10px; }
    
    .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
    .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
    .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .grid-2-custom { display: grid; grid-template-columns: 200px 1fr; gap: 20px; }

    .field { display: flex; flex-direction: column; gap: 8px; }
    .field label { font-weight: 600; color: #555; font-size: 13px; }
    .field input, .field select { padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; outline: none; }
    .field input:focus, .field select:focus { border-color: #f39c12; box-shadow: 0 0 5px rgba(243, 156, 18, 0.2); }

    /* Campos preenchidos pelo ViaCEP */
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

    /* Feedback inline do CEP */
    .field-feedback { font-size: 12px; min-height: 16px; }
    .field-feedback.ok   { color: #198754; }
    .field-feedback.erro { color: #dc3545; }
    
    .divider { margin: 30px 0; border: 0; border-top: 1px solid #eee; }
    .mb-3 { margin-bottom: 20px; }
    .mt-3 { margin-top: 15px; }
    .mt-4 { margin-top: 30px; }
    
    .form-actions { display: flex; justify-content: flex-end; gap: 15px; }
    .btn-cancelar { background: #f8f9fa; border: 1px solid #ddd; padding: 12px 25px; border-radius: 6px; cursor: pointer; color: #666; font-weight: bold; }
    .btn-sucesso { background: #27ae60; border: none; padding: 12px 30px; border-radius: 6px; cursor: pointer; color: #fff; font-weight: bold; transition: 0.3s; }
    .btn-sucesso:hover { background: #219150; transform: translateY(-2px); }
</style>

<?php include_once '../includes/footerUnes-2.php'; ?>
<script src="../js/cadastro-instituicao.js"></script>