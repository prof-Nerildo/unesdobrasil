<?php include_once '../includes/headerUnes.php'; ?>
<?php include_once '../includes/sidebarUnes.php'; ?>

<main class="content">
    <section class="main-section">
        <header class="top-bar">
            <h1><i class="fas fa-industry"></i> Produção de Documentos</h1>
        </header>

        <div class="container-fluid" style="padding: 20px;">
            
            <div class="row-dashboard" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 30px;">
                <div class="card-stats filter-card" data-status="9" onclick="setFilter(9)" style="border-left: 4px solid #00d2ff;">
                    <i class="fas fa-plus-circle icon-bg"></i>
                    <div class="stat-value" id="qtdCriado">0</div>
                    <div class="stat-label">DOCS. CRIADOS</div>
                </div>
                <div class="card-stats filter-card" data-status="5" onclick="setFilter(5)" style="border-left: 4px solid #ffbc00;">
                    <i class="fas fa-paper-plane icon-bg"></i>
                    <div class="stat-value" id="qtdSolicitado">0</div>
                    <div class="stat-label">DOCS. SOLICITADOS</div>
                </div>
                <div class="card-stats filter-card" data-status="6" onclick="setFilter(6)" style="border-left: 4px solid #4e54c8;">
                    <i class="fas fa-tools icon-bg"></i>
                    <div class="stat-value" id="qtdProducao">0</div>
                    <div class="stat-label">EM PRODUÇÃO</div>
                </div>
                <div class="card-stats filter-card" data-status="7" onclick="setFilter(7)" style="border-left: 4px solid #27ae60;">
                    <i class="fas fa-id-card icon-bg"></i>
                    <div class="stat-value" id="qtdProduzido">0</div>
                    <div class="stat-label">PRODUZIDOS</div>
                </div>
                <div class="card-stats filter-card" data-status="8" onclick="setFilter(8)" style="border-left: 4px solid #2c3e50;">
                    <i class="fas fa-truck icon-bg"></i>
                    <div class="stat-value" id="qtdEntregue">0</div>
                    <div class="stat-label">ENTREGUES</div>
                </div>
            </div>

            <div class="card-filter" style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background: #fff; border: 1px solid #e2e8f0; display: flex; gap: 20px; align-items: center;">
                <div style="flex: 1;">
                    <select id="filtroStatus" style="display:none;">
                        <option value="9">Criados</option>
                        <option value="5" selected>Solicitados</option>
                        <option value="6">Em Produção</option>
                        <option value="7">Produzidos</option>
                        <option value="8">Entregues</option>
                    </select>
                    <h3 id="tituloStatus" style="margin: 0; font-size: 14px; color: #4a5568; font-weight: 800; text-transform: uppercase;">
                        <i class="fas fa-list"></i> EXIBINDO: SOLICITADOS
                    </h3>
                </div>
                
                <div style="flex: 2; display: flex; gap: 10px; align-items: center;">
                    <div style="position: relative; flex: 1;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 13px; color: #a0aec0;"></i>
                        <input type="text" id="buscaGlobal" placeholder="Buscar nesta lista por nome, CPF ou idCard..." onkeyup="filtrarTabela()" style="width: 100%; padding: 10px 10px 10px 35px; border-radius: 5px; border: 1px solid #cbd5e0;">
                    </div>

                    <button id="btnMoverProducao" onclick="moverParaProducaoLote()" style="display: none; background: #4e54c8; border: none; padding: 10px 20px; border-radius: 5px; color: #fff; cursor: pointer; font-weight: bold; white-space: nowrap; font-size: 12px;">
                        <i class="fas fa-tools"></i> MOVER P/ PRODUÇÃO
                    </button>

                    <button id="btnFinalizarEntrega" onclick="marcarComoEntregueLote()" style="display: none; background: #2c3e50; border: none; padding: 10px 20px; border-radius: 5px; color: #fff; cursor: pointer; font-weight: bold; white-space: nowrap; font-size: 12px;">
                        <i class="fas fa-check-double"></i> MARCAR COMO ENTREGUE
                    </button>
                                        
                    
                    <button class="btn-primary" onclick="gerarLoteSelecionado()" style="background: #27ae60; border: none; padding: 10px 20px; border-radius: 5px; color: #fff; cursor: pointer; font-weight: bold; white-space: nowrap; font-size: 12px;">
                        <i class="fas fa-file-archive"></i> GERAR LOTE (ZIP)
                    </button>
                </div>
            </div>

            <div class="card" style="background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden;">
                <table class="table-unes">
                    <thead>
                        <tr>
                            <th style="cursor:pointer" onclick="ordenarEsteira('idCard')">IDCARD <i class="fas fa-sort" id="sort-idCard"></i></th>
                            <th style="cursor:pointer" onclick="ordenarEsteira('NomeDocumento')">NOME <i class="fas fa-sort" id="sort-NomeDocumento"></i></th>
                            <th style="cursor:pointer" onclick="ordenarEsteira('InsEnsinoDocumento')">INST. ENSINO <i class="fas fa-sort" id="sort-InsEnsinoDocumento"></i></th>
                            <th style="cursor:pointer" onclick="ordenarEsteira('serieDocumento')">CURSO <i class="fas fa-sort" id="sort-serieDocumento"></i></th>
                            <th style="cursor:pointer" onclick="ordenarEsteira('nCPF')">CPF <i class="fas fa-sort" id="sort-nCPF"></i></th>
                            <th>RG</th>
                            <th style="cursor:pointer" onclick="ordenarEsteira('dataNascDocumento')">NASC. <i class="fas fa-sort" id="sort-dataNascDocumento"></i></th>
                            <th class="text-center">FOTO</th>
                            
                            <th style="cursor:pointer" onclick="ordenarEsteira('dataCriacao')">SOLICITAÇÃO <i class="fas fa-sort" id="sort-dataCriacao"></i></th>
                            <th style="cursor:pointer" onclick="ordenarEsteira('dataAlteracao')">ALTUALIZAÇÃO <i class="fas fa-sort" id="sort-dataAlteracao"></i></th> 
                            <th class="text-center">AÇÃO</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaProducaoCorpo"></tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<style>
    .card-stats { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: relative; overflow: hidden; cursor: pointer; transition: 0.3s; border: 1px solid transparent; }
    .card-stats:hover { transform: translateY(-3px); border-color: #cbd5e0; }
    .card-stats.active { background: #f7fafc; border-color: #4a5568; }
    .card-stats .icon-bg { position: absolute; right: -10px; bottom: -10px; font-size: 50px; opacity: 0.1; transform: rotate(-15deg); }
    .stat-value { font-size: 28px; font-weight: 800; color: #2d3436; margin-bottom: 5px; }
    .stat-label { font-size: 10px; font-weight: 700; color: #636e72; text-transform: uppercase; }

    .table-unes { width: 100%; border-collapse: collapse; font-size: 13px; }
    .table-unes th { background: #f8fafc; color: #4a5568; padding: 15px; text-align: left; border-bottom: 2px solid #edf2f7; text-transform: uppercase; font-weight: 700; font-size: 11px; }
    .table-unes td { padding: 12px 15px; border-bottom: 1px solid #edf2f7; color: #2d3748; vertical-align: middle; }
    .table-unes tr:hover { background: #f7fafc; }
    
    .text-bold { font-weight: 700; color: #2c3e50; }
    .img-table-thumb { width: 40px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
    .text-center { text-align: center; }
/* Deixa a hora um pouco menor para dar destaque à data */
.table-unes td small {
    display: block;
    margin-top: 2px;
    font-size: 11px;
}

.btn-mini-acao {
    border: none;
    color: white;
    padding: 6px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    transition: 0.2s;
}
.btn-mini-acao:hover {
    transform: scale(1.1);
    filter: brightness(1.2);
}
.text-center { text-align: center; }
.table-unes td small { display: block; margin-top: 2px; font-size: 11px; }
#paginador-v2 {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    padding: 20px;
}

.btn-pag-item, .btn-pag-nav {
    background: #fff;
    color: #4a5568;
    border: 1px solid #e2e8f0;
    padding: 6px 12px;
    cursor: pointer;
    border-radius: 6px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s;
}

.btn-pag-active {
    background: #3182ce;
    color: #fff;
    border: 1px solid #3182ce;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 700;
    box-shadow: 0 4px 6px rgba(49, 130, 206, 0.2);
}

.btn-pag-item:hover, .btn-pag-nav:hover {
    background: #f7fafc;
    border-color: #cbd5e0;
}
table th i {
    margin-left: 5px;
    font-size: 10px;
    color: #cbd5e0; /* Cor neutra inicial */
}

table th:hover i {
    color: #3182ce; /* Fica azul quando passa o mouse */
}

/* Estilo para os mini botões de ação */
.btn-mini-acao {
    border: none;
    color: white;
    padding: 6px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    transition: 0.2s;
}
.btn-mini-acao:hover { transform: scale(1.1); filter: brightness(1.2); }

/* 1. TRAVA A LARGURA DA TABELA */
.table-unes {
    width: 100%;
    table-layout: fixed; /* Força o respeito às larguras que definirmos */
    border-collapse: collapse;
}

/* 2. AJUSTE INDIVIDUAL POR COLUNA */
.table-unes th:nth-child(1), .table-unes td:nth-child(1) { width: 110px; } /* IDCARD */
.table-unes th:nth-child(2), .table-unes td:nth-child(2) { width: auto;  } /* NOME (Cresce livre) */
.table-unes th:nth-child(3), .table-unes td:nth-child(3) { width: 150px; } /* INST. ENSINO */
.table-unes th:nth-child(4), .table-unes td:nth-child(4) { width: 130px; } /* CURSO */
.table-unes th:nth-child(5), .table-unes td:nth-child(5) { width: 115px; } /* CPF */
.table-unes th:nth-child(6), .table-unes td:nth-child(6) { width: 100px; } /* RG */
.table-unes th:nth-child(7), .table-unes td:nth-child(7) { width: 90px;  } /* NASC. */
.table-unes th:nth-child(8), .table-unes td:nth-child(8) { width: 65px;  } /* FOTO */
.table-unes th:nth-child(9), .table-unes td:nth-child(9) { width: 100px; } /* SOLICIT. */
.table-unes th:nth-child(10), .table-unes td:nth-child(10) { width: 100px; } /* ÚLT. ALT. */
.table-unes th:nth-child(11), .table-unes td:nth-child(11) { width: 60px;  } /* AÇÃO */

/* 3. TRATAMENTO DE TEXTO LONGO */
.table-unes td {
    white-space: nowrap;      /* Não deixa o texto quebrar linha */
    overflow: hidden;         /* Esconde o que sobrar */
    text-overflow: ellipsis;  /* Coloca "..." se o nome da faculdade for gigante */
    padding: 10px 8px;
}

/* 4. AJUSTE ESPECIAL PARA AS DATAS (PARA CABER A HORA EMBAIXO) */
.table-unes td:nth-child(9), 
.table-unes td:nth-child(10) {
    white-space: normal;      /* Deixa a data e hora quebrarem linha */
    line-height: 1.1;
    font-size: 11px;
}

/* 5. HOVER NOS TÍTULOS */
.table-unes th {
    white-space: nowrap;
    font-size: 10px !important;
    background: #f1f5f9;
}
</style>

<script src="../js/producao-global.js"></script>

<?php include_once '../includes/footerUnes-2.php'; ?>