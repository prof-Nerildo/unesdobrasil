<?php
$c1 = $stats['criado'] ?? 0;
$c2 = $stats['solicitado'] ?? 0;
$c3 = $stats['producao'] ?? 0;
$c4 = $stats['produzido'] ?? 0;
$c5 = $stats['entregue'] ?? 0;
?>

<section class="stats-grid">
    <div class="card" onclick="window.location.href='nova-solicitacao.php'" 
         style="cursor:pointer; border: 2px dashed #27ae60; background: rgba(39, 174, 96, 0.05);">
        <div class="card-icon" style="background: #27ae60; color: white;">
            <i class="fas fa-plus"></i>
        </div>
        <div class="card-info">
            <h3 style="color: #27ae60;">NOVA</h3>
            <p style="color: #219150;">SOLICITAÇÃO</p>
        </div>
    </div>

    <div class="card" onclick="navegarFiltroDoc('criado')" style="cursor:pointer; border-left: 5px solid #0dcaf0;">
        <div class="card-icon" style="color: #0dcaf0;"><i class="fas fa-plus-circle"></i></div>
        <div class="card-info">
            <h3 id="qtdCriado"><?php echo $c1; ?></h3>
            <p>Docs. Criados</p>
        </div>
    </div>

    <div class="card" onclick="navegarFiltroDoc('solicitado')" style="cursor:pointer; border-left: 5px solid #ffc107;">
        <div class="card-icon" style="color: #ffc107;"><i class="fas fa-paper-plane"></i></div>
        <div class="card-info">
            <h3 id="qtdSolicitado"><?php echo $c2; ?></h3>
            <p>Docs. Solicitados</p>
        </div>
    </div>

    <div class="card" onclick="navegarFiltroDoc('producao')" style="cursor:pointer; border-left: 5px solid #0d6efd;">
        <div class="card-icon" style="color: #0d6efd;"><i class="fas fa-tools"></i></div>
        <div class="card-info">
            <h3 id="qtdProducao"><?php echo $c3; ?></h3>
            <p>Em Produção</p>
        </div>
    </div>

    <div class="card" onclick="navegarFiltroDoc('produzido')" style="cursor:pointer; border-left: 5px solid #198754;">
        <div class="card-icon" style="color: #198754;"><i class="fas fa-id-card-alt"></i></div>
        <div class="card-info">
            <h3 id="qtdProduzido"><?php echo $c4; ?></h3>
            <p>Produzidos</p>
        </div>
    </div>

    <div class="card" onclick="navegarFiltroDoc('entregue')" style="cursor:pointer; border-left: 5px solid #212529;">
        <div class="card-icon" style="color: #212529;"><i class="fas fa-shipping-fast"></i></div>
        <div class="card-info">
            <h3 id="qtdEntregue"><?php echo $c5; ?></h3>
            <p>Entregues</p>
        </div>
    </div>
</section>