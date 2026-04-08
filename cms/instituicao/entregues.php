<?php include_once '../includes/headerInstituicao.php'; ?>
<?php include_once '../includes/sidebarInstituicao.php'; ?>

<main class="content">
    <section class="main-section">
        <header class="top-bar">
            <h1><i class="fas fa-check-double"></i> Documentos Entregues</h1>
        </header>

        <div class="search-box-wrapper" style="margin-top: 20px; margin-left: 20px;">
            <div class="field-search">
                <i class="fas fa-search"></i>
                <input type="text" id="busca_entregues" placeholder="Buscar por nome, CPF ou idCard..." onkeyup="filtrarTabelaLocal()">
            </div>
        </div>

        <div class="card-table">
            <div class="table-header-info" style="padding: 15px; background: #f8fafc; border-bottom: 2px solid #edf2f7;">
                <h3 style="font-size: 11px; font-weight: 700; color: #4a5568; text-transform: uppercase;">
                    <i class="fas fa-archive"></i> Histórico de Entregas
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>idCard</th>
                            <th>NOME COMPLETO</th>
                            <th>INST. ENSINO</th>
                            <th>CPF</th>
                            <th>FOTO</th>
                            <th>DATA ENTREGA</th>
                            <th>AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody id="tabela_entregues_corpo">
                        <tr><td colspan="7" class="text-center" style="padding:40px;">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<style>
    .card-table { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin: 20px; overflow: hidden; }
    .table-custom { width: 100%; border-collapse: collapse; }
    .table-custom thead th { background: #f8fafc; color: #4a5568; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 15px; text-align: left; border-bottom: 2px solid #edf2f7; }
    .table-custom tbody td { padding: 15px; font-size: 13px; color: #2d3748; border-bottom: 1px solid #edf2f7; }
    .img-table-thumb { width: 35px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; }
    .btn-segunda-via { background: #3498db; color: white; border: none; padding: 8px 12px; border-radius: 6px; font-size: 10px; font-weight: bold; cursor: pointer; transition: 0.3s; }
    .btn-segunda-via:hover { background: #2980b9; }
    .search-box-wrapper {
    max-width: 400px;
    margin-bottom: 20px;
}

.field-search {
    position: relative;
    display: flex;
    align-items: center;
}

.field-search i {
    position: absolute;
    left: 15px;
    color: #a0aec0;
    font-size: 14px;
}

.field-search input {
    width: 100%;
    padding: 12px 15px 12px 40px; /* O padding de 40px abre espaço para a lupa */
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease;
}

.field-search input:focus {
    border-color: #1abc9c;
    box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.1);
}
</style>

<script src="../js/entregues.js"></script>