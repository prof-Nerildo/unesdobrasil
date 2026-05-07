<?php include_once '../includes/headerInstituicao.php'; ?>
<?php include_once '../includes/sidebarInstituicao.php'; ?>

<main class="content">
    <section class="main-section">
        <header class="top-bar">
            <h1><i class="fas fa-industry"></i> Gestão de Produção</h1>
        </header>

        <div id="container-estatisticas"></div>

        <div class="search-box-wrapper">
            <div class="field-search">
                <i class="fas fa-search"></i>
                <input type="text" id="busca_producao" placeholder="Buscar nesta lista por nome, CPF ou idCard..." onkeyup="filtrarTabelaLocal()">
            </div>
        </div>

        <div class="card-table">
            <div class="table-header-info" style="padding: 15px; background: #f8fafc; border-bottom: 2px solid #edf2f7;">
                <h3 id="titulo-lista" style="font-size: 11px; font-weight: 700; color: #4a5568; text-transform: uppercase;">
                    <i class="fas fa-list"></i> Selecione um status acima para listar
                </h3>
            </div>
            
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th data-col="idCard" onclick="ordenarProducao('idCard')" style="cursor:pointer">idCard <i class="fas fa-sort" data-sort="idCard" style="opacity:0.3"></i></th>
                            <th data-col="NomeDocumento" onclick="ordenarProducao('NomeDocumento')" style="cursor:pointer">NOME COMPLETO <i class="fas fa-sort" data-sort="NomeDocumento" style="opacity:0.3"></i></th>
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
                    <tbody id="tabela_producao_corpo">
                        <tr><td colspan="10" class="text-center" style="padding:40px; color: #a0aec0;">Aguardando seleção de status...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div id="paginador-producao" class="paginacao-container"></div>
</main>

<style>
    /* --- CSS UNIFICADO (PADRÃO UNES) --- */
    
    /* Layout do Card de Tabela */
    .card-table { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin: 20px; overflow: hidden; }
    
    /* Estilo da Tabela */
    .table-custom { width: 100%; border-collapse: collapse; }
    .table-custom thead th { background: #f8fafc; color: #4a5568; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 15px; text-align: left; border-bottom: 2px solid #edf2f7; }
    .table-custom tbody td { padding: 15px; font-size: 13px; color: #2d3748; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
    
    /* Tipografia e Foto */
    .text-bold { font-weight: 700; }
    .img-table-thumb { width: 35px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; display: block; }

    /* Barra de Busca com Lupa Interna */
    .search-box-wrapper { max-width: 400px; margin: 20px 0 10px 20px; }
    .field-search { position: relative; display: flex; align-items: center; }
    .field-search i { position: absolute; left: 15px; color: #a0aec0; font-size: 14px; }
    .field-search input { 
        width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #e2e8f0; 
        border-radius: 8px; font-size: 14px; background-color: #fff; outline: none; transition: all 0.3s ease;
    }
    .field-search input:focus { border-color: #1abc9c; box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.1); }

    /* Estilo dos Cards de Status (Grid) */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; padding: 20px; }
    .card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .card:hover { transform: translateY(-3px); }
    .card h3 { font-size: 24px; margin-bottom: 5px; color: #2d3748; }
    .card p { color: #718096; font-size: 12px; font-weight: 700; text-transform: uppercase; }

    /* Botões de Ação */
    .btn-edit-table { color: #f6ad55; background:none; border:none; cursor:pointer; font-size:16px; transition: 0.2s; }
    .btn-edit-table:hover { color: #dd6b20; }
    .btn-sucesso { background: #1abc9c; color: #fff; border: none; padding: 8px 15px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 10px; }
    .btn-sucesso:hover { background: #16a085; }

    /* Paginação */
    .paginacao-container { display: flex; justify-content: center; align-items: center; gap: 8px; margin: 30px 0; }
    .btn-pag { min-width: 40px; height: 40px; padding: 0 10px; border: 1px solid #e0e0e0; background: #fff; color: #555; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .btn-pag:hover { background: #f0f0f0; border-color: #bbb; }
    .btn-pag.active { background: #2c3e50; color: #fff; border-color: #2c3e50; box-shadow: 0 4px 10px rgba(44, 62, 80, 0.2); }
    
    /* Ordenação */
    th[data-col]:hover { background-color: #f1f1f1 !important; color: #3182ce; }
    th i { margin-left: 5px; font-size: 10px; color: #3182ce; }
</style>

<script src="../js/env.php"></script>
<script src="../js/producao.js"></script>

<?php include_once '../includes/footerUnes-2.php'; ?>