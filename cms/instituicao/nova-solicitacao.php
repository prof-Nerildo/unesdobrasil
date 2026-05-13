<?php include_once '../includes/headerInstituicao.php'; ?>
<?php include_once '../includes/sidebarInstituicao.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<main class="content">
    <section class="main-section">
        <header class="top-bar">
            <h1><i class="fas fa-plus-circle"></i> Criar Documento Estudantil</h1>
        </header>

        <div class="card-form" id="container-principal">
            <div class="grid-form-main">
                
                <div class="form-section column-left">
                    <h3><i class="fas fa-user-graduated"></i> Dados do Beneficiário</h3>
                    
                    <div class="field mb-3">
                        <label>Nome Completo <span style="color:red">*</span></label>
                        <input type="text" id="nome_aluno" placeholder="Nome completo do aluno">
                    </div>

                    <div class="field mb-3">
                        <label>Instituição de Ensino</label>
                        <input type="text" id="nome_escola_fixo" placeholder="Ex: Escola Estadual Machado de Assis">
                    </div>

                    <div class="field mb-3">
                            <label>Série / Curso</label>
                            <input type="text" id="curso_aluno" placeholder="Ex: 3º Ano Ensino Médio">
                    </div>
                    <div class="field mb-3">
                            <label>CPF <span style="color:red">*</span></label>
                            <input type="text" id="cpf_aluno" placeholder="000.000.000-00">
                    </div>
                    <div class="field mb-3">
                            <label>RG / Identidade <span style="color:red">*</span></label>
                            <input type="text" id="rg_aluno" placeholder="Número do RG">
                    </div>
                    <div class="field mb-3">
                            <label>Data de Nascimento <span style="color:red">*</span></label>
                            <input type="date" id="data_nascimento">
                    </div>
                </div> 
                <div class="form-section column-right">
                    <h3><i class="fas fa-camera"></i> Foto do Documento</h3>
                    
                    <div class="guide-container mb-4">
                        <p class="guide-title">Guia de foto para carteirinha:</p>
                        <div class="guide-grid-icons">
                            <div class="guide-item">
                                <div class="icon-wrap valid"><i class="fas fa-check-circle status-icon"></i><i class="fas fa-user main-icon"></i></div>
                                <span>Válida</span>
                            </div>
                            <div class="guide-item">
                                <div class="icon-wrap invalid"><i class="fas fa-times-circle status-icon"></i><i class="fas fa-users main-icon"></i></div>
                                <span>Pessoas</span>
                            </div>
                            <div class="guide-item">
                                <div class="icon-wrap invalid"><i class="fas fa-times-circle status-icon"></i><i class="fas fa-glasses main-icon"></i></div>
                                <span>Lentes</span>
                            </div>
                            <div class="guide-item">
                                <div class="icon-wrap invalid"><i class="fas fa-times-circle status-icon"></i><i class="fas fa-magic main-icon"></i></div>
                                <span>Filtro</span>
                            </div>
                            <div class="guide-item">
                                <div class="icon-wrap invalid"><i class="fas fa-times-circle status-icon"></i><i class="fas fa-user-slash main-icon"></i></div>
                                <span>Perfil</span>
                            </div>
                            <div class="guide-item">
                                <div class="icon-wrap invalid"><i class="fas fa-times-circle status-icon"></i><i class="fas fa-low-vision main-icon"></i></div>
                                <span>Qualidade</span>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label>Selecione a Imagem (3x4)</label>
                        <p style="font-size: 12px; color: #f00; text-align: center;">Imagens em (PNG | JPG | JPEG)</p>
                        <input type="file" id="input_foto" accept="image/jpeg,image/png" onchange="iniciarCrop(event)">
                    </div>

                    <div id="wrapper-crop" style="display:none;">
                        <p class="preview-label"><i class="fas fa-arrows-alt"></i> Ajuste o recorte (3x4):</p>
                        <div class="cropper-container-wrapper">
                            <img id="image-to-crop">
                        </div>
                        <button type="button" class="btn-confirm-crop" onclick="cortarFoto()">
                            <i class="fas fa-crop-alt"></i> CONFIRMAR CORTE
                        </button>
                    </div>

                    <div id="preview-final" style="display:none;">
                        <p class="preview-title"><i class="fas fa-check-circle"></i> FOTO PRONTA!</p>
                        <div class="photo-frame">
                            <img id="foto_cortada_resultado">
                        </div>
                        <button type="button" class="btn-trocar-foto" onclick="document.getElementById('input_foto').click()">
                            <i class="fas fa-sync-alt"></i> Trocar Foto
                        </button>
                    </div>
                </div> 
            </div> 
            <hr class="divider">

            <div class="form-actions-footer">
                <button class="btn-limpar-footer" onclick="limparCamposAluno()">
                    <i class="fas fa-eraser"></i> LIMPAR CAMPOS
                </button>
                <button class="btn-sucesso-footer" onclick="adicionarDocumento()">
                    <i class="fas fa-save"></i> FINALIZAR E ADICIONAR DOCUMENTO
                </button>
            </div>
        </div>
    </section>

    <section class="main-section" style="margin-top: 40px;">
        <header class="top-bar">
            <h1><i class="fas fa-list"></i> Documentos Recém Criados</h1>
        </header>

        <div class="search-box-wrapper">
            <div class="field-search">
                <i class="fas fa-search"></i>
                <input type="text" id="busca_documento" placeholder="Buscar por nome, CPF ou idCard..." onkeyup="filtrarDocumentos()">
            </div>
        </div>

        <div class="card-table">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>idCard</th>
                            <th>NOME COMPLETO</th>
                            <th>INST. ENSINO</th>
                            <th>SÉRIE/CURSO</th>
                            <th>CPF</th>
                            <th>RG/ IDENTIDADE</th>
                            <th>DATA NASC.</th>
                            <th>FOTO</th>
                            <th>DATA CRIAÇÃO</th>
                            <th>AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody id="tabela_docs_corpo"></tbody>
                </table>
            </div>
            <div class="pagination-container" id="paginacao_docs"></div>
        </div>
    </section>
</main>

<style>
    /* 1. LAYOUT PRINCIPAL */
    .card-form { background: #fff; padding: 35px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin: 20px; }
    .grid-form-main { display: grid; grid-template-columns: 1.3fr 1fr; gap: 40px; align-items: start; }
    .form-section h3 { color: #2c3e50; font-size: 17px; margin-bottom: 25px; border-left: 4px solid #1abc9c; padding-left: 12px; text-transform: uppercase; }
    
    /* 2. CAMPOS */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .field { display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px; }
    .field label { font-weight: 700; color: #4a5568; font-size: 12px; text-transform: uppercase; }
    .field input { padding: 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; }
    .field input:focus { border-color: #1abc9c; box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.1); }

    /* 3. GUIA DE FOTOS (GRID 3x2) */
    .guide-grid-icons { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #edf2f7; }
    .guide-item { text-align: center; display: flex; flex-direction: column; align-items: center; }
    .icon-wrap { position: relative; width: 45px; height: 45px; background: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 5px; border: 1px solid #e2e8f0; }
    .main-icon { font-size: 18px; color: #cbd5e0; }
    .status-icon { position: absolute; top: -5px; left: -5px; font-size: 14px; background: #fff; border-radius: 50%; z-index: 5; }
    .valid { border-color: #27ae60; background: #f0fdf4; }
    .valid .status-icon, .valid .main-icon { color: #27ae60; }
    .invalid { border-color: #feb2b2; }
    .invalid .status-icon { color: #e53e3e; }
    .guide-item span { font-size: 9px; color: #718096; font-weight: 700; text-transform: uppercase; }
    .guide-title { font-size: 11px; font-weight: 800; margin-bottom: 12px; color: #a0aec0; text-transform: uppercase; border-bottom: 1px solid #edf2f7; padding-bottom: 5px; }

    /* 4. ÁREA DE CROP (FIXADO NA DIREITA) */
    #wrapper-crop { width: 100%; background: #f8fafc; border: 2px dashed #cbd5e0; border-radius: 12px; padding: 15px; margin-top: 15px; text-align: center; box-sizing: border-box; }
    .cropper-container-wrapper { width: 100%; max-height: 350px; min-height: 200px; overflow: hidden; background-color: #000; border-radius: 6px; }
    #image-to-crop { display: block; max-width: 100%; }
    .btn-confirm-crop { margin-top: 15px; width: 100%; background: #2c3e50; height: 45px; border: none; color: #fff; border-radius: 8px; cursor: pointer; font-weight: bold; }

    /* 5. PREVIEW FINAL (FIXADO NA DIREITA) */
    #preview-final { width: 100%; background: #f0fdf4; border: 2px dashed #27ae60; border-radius: 12px; padding: 20px; margin-top: 15px; text-align: center; box-sizing: border-box; }
    .preview-title { font-size: 11px; font-weight: 800; color: #27ae60; text-transform: uppercase; margin-bottom: 15px; }
    .photo-frame { display: inline-block; padding: 5px; background: #fff; border-radius: 5px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #ddd; }
    #foto_cortada_resultado { width: 113px; height: 151px; object-fit: cover; display: block; }
    .btn-trocar-foto { display: block; margin: 15px auto 0; background: none; border: none; color: #4a5568; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: underline; }

    /* 6. TABELA E BUSCA */
    .card-table { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin: 20px; overflow: hidden; }
    .table-custom { width: 100%; border-collapse: collapse; }
    .table-custom thead th { background: #f8fafc; color: #4a5568; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 15px; text-align: left; border-bottom: 2px solid #edf2f7; }
    .table-custom tbody td { padding: 15px; font-size: 13px; color: #2d3748; border-bottom: 1px solid #edf2f7; }
    .text-bold { font-weight: 700; }
    .img-table-thumb { width: 35px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; }
    .field-search input { padding: 12px 12px 12px 40px; border: 1px solid #e2e8f0; border-radius: 8px; width: 100%; font-size: 14px; outline: none; }
    .btn-edit-table { color: #f6ad55; background:none; border:none; cursor:pointer; font-size:16px; margin-right:10px; }
    .btn-delete-table { color: #e53e3e; background:none; border:none; cursor:pointer; font-size:16px; }
    .form-actions-footer { display: flex; gap: 20px; margin: 20px; }
    .btn-sucesso-footer { flex: 2; background: #1abc9c; border: none; height: 55px; border-radius: 10px; color: #fff; font-weight: bold; cursor: pointer; }
    .btn-limpar-footer { flex: 0.8; background: #fff; border: 1px solid #e2e8f0; height: 55px; border-radius: 10px; color: #718096; font-weight: bold; cursor: pointer; }
    .divider { border: 0; border-top: 1px solid #edf2f7; margin: 20px 0; }

    /* --- AJUSTE BOX DE BUSCA --- */
.search-box-wrapper {
    max-width: 350px; /* Garante que não estique na tela toda */
    margin-bottom: 20px;
    margin-left: 20px; /* Alinha com o início da tabela */
}

.field-search {
    position: relative;
    display: flex;
    align-items: center;
}

.field-search i {
    position: absolute;
    left: 15px;
    color: #a0aec0; /* Cor cinza da lupa */
    font-size: 14px;
}

.field-search input {
    width: 100%;
    padding: 12px 15px 12px 40px; /* 40px na esquerda para não escrever em cima da lupa */
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    background-color: #fff;
    transition: all 0.3s ease;
    outline: none;
}

.field-search input:focus {
    border-color: #1abc9c;
    box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.1);
}

.field-search input::placeholder {
    color: #a0aec0;
}
</style>

<script src="../js/documento.js"></script>
<?php include_once '../includes/footerUnes-2.php'; ?>