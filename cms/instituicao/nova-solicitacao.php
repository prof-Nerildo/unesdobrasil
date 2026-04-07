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
                
                <div class="form-section">
                    <h3><i class="fas fa-user-graduated"></i> Dados do Beneficiário</h3>
                    
                    <div class="field mb-3">
                        <label>Nome Completo</label>
                        <input type="text" id="nome_aluno" placeholder="Nome completo do aluno" required>
                    </div>

                    <div class="field mb-3">
                        <label>Instituição de Ensino</label>
                        <input type="text" id="nome_escola_fixo" placeholder="Ex: Escola Estadual Machado de Assis" required >
                    </div>

                    <div class="grid-2 mb-3">
                        <div class="field">
                            <label>Série / Curso</label>
                            <input type="text" id="curso_aluno" placeholder="Ex: 3º Ano Ensino Médio" required>
                        </div>
                        <div class="field">
                            <label>Data de Nascimento</label>
                            <input type="date" id="data_nascimento">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="field">
                            <label>CPF</label>
                            <input type="text" id="cpf_aluno" placeholder="000.000.000-00" required>
                        </div>
                        <div class="field">
                            <label>RG / Identidade</label>
                            <input type="text" id="rg_aluno" placeholder="Número do RG" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-camera"></i> Foto do Documento</h3>
                    
                    <div class="photo-upload-area">
                        <div class="guide-container mb-4">
                            <p class="guide-title">Guia de foto para carteirinha:</p>
                            <div class="guide-grid-icons">
                                <div class="guide-item">
                                    <div class="icon-wrap valid">
                                        <i class="fas fa-check-circle status-icon"></i>
                                        <i class="fas fa-user main-icon"></i>
                                    </div>
                                    <span>Válida</span>
                                </div>
                                <div class="guide-item">
                                    <div class="icon-wrap invalid">
                                        <i class="fas fa-times-circle status-icon"></i>
                                        <i class="fas fa-users main-icon"></i>
                                    </div>
                                    <span>Pessoas</span>
                                </div>
                                <div class="guide-item">
                                    <div class="icon-wrap invalid">
                                        <i class="fas fa-times-circle status-icon"></i>
                                        <i class="fas fa-glasses main-icon"></i>
                                    </div>
                                    <span>Lentes</span>
                                </div>
                                <div class="guide-item">
                                    <div class="icon-wrap invalid">
                                        <i class="fas fa-times-circle status-icon"></i>
                                        <i class="fas fa-magic main-icon"></i>
                                    </div>
                                    <span>Filtro</span>
                                </div>
                                <div class="guide-item">
                                    <div class="icon-wrap invalid">
                                        <i class="fas fa-times-circle status-icon"></i>
                                        <i class="fas fa-user-slash main-icon"></i>
                                    </div>
                                    <span>Perfil</span>
                                </div>
                                <div class="guide-item">
                                    <div class="icon-wrap invalid">
                                        <i class="fas fa-times-circle status-icon"></i>
                                        <i class="fas fa-low-vision main-icon"></i>
                                    </div>
                                    <span>Qualidade</span>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label>Selecione a Imagem (3x4)</label>
                            <input type="file" id="input_foto" accept="image/jpeg,image/png" onchange="iniciarCrop(event)">
                            <p class="warning-text">Use arquivos JPG ou PNG de alta resolução.</p>
                        </div>

                        <div id="wrapper-crop" style="display:none; margin: 15px 0; border: 1px solid #ddd; padding: 10px; background: #f9f9f9; border-radius: 8px;">
                            <div style="max-height: 250px; overflow: hidden;">
                                <img id="image-to-crop" style="max-width: 100%;">
                            </div>
                            <button type="button" class="btn-sucesso" style="margin-top:10px; width:100%; justify-content:center; background:#2c3e50;" onclick="cortarFoto()">
                                <i class="fas fa-crop-alt"></i> CONFIRMAR CORTE
                            </button>
                        </div>

                        <div id="preview-final" style="display:none; margin: 15px 0; text-align:center; background: #f0fdf4; padding: 15px; border-radius: 8px; border: 1px dashed #27ae60;">
                            <p style="font-size: 11px; font-weight: bold; color: #27ae60; margin-bottom: 10px;">FOTO CORTADA:</p>
                            <img id="foto_cortada_resultado" style="width: 113px; height: 151px; border: 3px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-radius: 4px;">
                            <button type="button" onclick="document.getElementById('input_foto').click()" style="display:block; margin: 10px auto; border:none; background:none; color:#2c3e50; font-size:12px; cursor:pointer; text-decoration:underline;">Trocar Foto</button>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="divider">

            <div class="form-actions-footer mt-4">
                <button class="btn-limpar-footer" onclick="window.location.reload()">
                    <i class="fas fa-eraser"></i> LIMPAR CAMPOS
                </button>
                <button class="btn-sucesso-footer" onclick="adicionarDocumento()">
                    <i class="fas fa-save"></i> FINALIZAR E ADICIONAR DOCUMENTO
                </button>
            </div>
        </div>
    </section>


<style>
    /* 1. CONTAINER DO CARD */
    .card-form { 
        background: #fff; 
        padding: 35px; 
        border-radius: 12px; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.06); 
        margin: 20px; 
        min-height: 500px; 
    }

    /* 2. GRID PRINCIPAL (DADOS vs FOTO) */
    .grid-form-main { 
        display: grid; 
        grid-template-columns: 1.4fr 1fr; 
        gap: 50px; 
    }

    /* 3. TÍTULOS DAS SEÇÕES */
    .form-section h3 { 
        color: #2c3e50; 
        font-size: 17px; 
        margin-bottom: 25px; 
        border-left: 4px solid #1abc9c; 
        padding-left: 12px; 
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* 4. CAMPOS DE INPUT */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    
    .field { display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px; }
    
    .field label { 
        font-weight: 700; 
        color: #4a5568; 
        font-size: 12px; 
        text-transform: uppercase;
    }

    .field input { 
        padding: 14px; 
        border: 1px solid #e2e8f0; 
        border-radius: 8px; 
        font-size: 14px; 
        outline: none; 
        transition: all 0.3s ease; 
        background: #fff;
    }

    .field input:focus { 
        border-color: #1abc9c; 
        box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.1); 
    }

    .field input[readonly] { 
        background: #f8fafc; 
        color: #718096; 
        cursor: not-allowed; 
    }

    /* 5. GUIA DE FOTO (ESTILO 3x2) */
    .guide-title { 
        font-size: 11px; 
        font-weight: 800; 
        margin-bottom: 15px; 
        color: #a0aec0; 
        text-transform: uppercase; 
        border-bottom: 1px solid #edf2f7; 
        padding-bottom: 8px; 
    }

    .guide-grid-icons { 
        display: grid; 
        grid-template-columns: repeat(3, 1fr); 
        gap: 15px; 
        background: #f8fafc;
        padding: 15px;
        border-radius: 10px;
    }

    .guide-item { 
        text-align: center; 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
    }

    .icon-wrap { 
        position: relative; 
        width: 50px; 
        height: 50px; 
        background: #fff; 
        border-radius: 10px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        margin-bottom: 6px; 
        border: 1px solid #e2e8f0; 
    }

    .main-icon { font-size: 20px; color: #cbd5e0; }
    
    .status-icon { 
        position: absolute; 
        top: -6px; 
        left: -6px; 
        font-size: 15px; 
        background: #fff; 
        border-radius: 50%; 
        z-index: 5;
    }

    /* Cores do Guia */
    .valid { border-color: #27ae60; background: #f0fdf4; }
    .valid .status-icon, .valid .main-icon { color: #27ae60; }
    
    .invalid { border-color: #feb2b2; }
    .invalid .status-icon { color: #e53e3e; }

    .guide-item span { 
        font-size: 9px; 
        color: #718096; 
        font-weight: 700; 
        text-transform: uppercase; 
    }

    /* 6. ALERTAS E DIVISOR */
    .warning-text { color: #e53e3e; font-size: 11px; font-weight: 700; margin-top: 6px; line-height: 1.4; }
    .divider { margin: 35px 0; border: 0; border-top: 1px solid #edf2f7; }

    /* 7. BOTÕES DE AÇÃO (RODAPÉ) */
    .form-actions-footer { 
        display: flex; 
        gap: 20px; 
        margin-top: 10px; 
        padding-top: 20px;
    }

    .btn-sucesso-footer { 
        flex: 2; 
        background: #1abc9c; 
        border: none; 
        height: 55px; 
        border-radius: 10px; 
        color: #fff; 
        font-weight: 700; 
        font-size: 15px; 
        cursor: pointer; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        gap: 12px; 
        transition: all 0.3s ease; 
    }

    .btn-sucesso-footer:hover { 
        background: #16a085; 
        transform: translateY(-2px); 
        box-shadow: 0 8px 20px rgba(26, 188, 156, 0.25);
    }

    .btn-limpar-footer { 
        flex: 0.8; 
        background: #fff; 
        border: 1px solid #e2e8f0; 
        height: 55px; 
        border-radius: 10px; 
        color: #718096; 
        font-weight: 700; 
        font-size: 14px;
        cursor: pointer; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        gap: 10px; 
        transition: all 0.3s ease; 
    }

    .btn-limpar-footer:hover { 
        background: #fff5f5; 
        color: #e53e3e; 
        border-color: #feb2b2; 
    }

    /* 8. CROPPER CUSTOM */
    #wrapper-crop { 
        border-radius: 10px; 
        overflow: hidden; 
        border: 2px solid #edf2f7; 
    }
</style>

<script src="../js/documento.js"></script>
<?php include_once '../includes/footerUnes-2.php'; ?>