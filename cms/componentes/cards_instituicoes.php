<section class="stats-grid">
    <div class="card card-warning" onclick="navegarFiltro('3')" style="cursor:pointer">
        <div class="card-icon"><i class="fas fa-check-circle"></i></div>
        <div class="card-info">
            <h3 id="qtdValidar">0</h3>
            <p>Validar Inst. Ensino</p>
        </div>
    </div>

    <div class="card card-success" onclick="navegarFiltro('nao')" style="cursor:pointer">
        <div class="card-icon"><i class="fas fa-door-open"></i></div>
        <div class="card-info">
            <h3 id="qtdSemCatraca">0</h3>
            <p>Inst. Ensino sem/Catraca</p>
        </div>
    </div>

    <div class="card card-danger" onclick="navegarFiltro('sim')" style="cursor:pointer">
        <div class="card-icon"><i class="fas fa-qrcode"></i></div>
        <div class="card-info">
            <h3 id="qtdComCatraca">0</h3>
            <p>Inst. Ensino com/Catraca</p>
        </div>
    </div>
</section>

<script>
/**
 * Lógica inteligente de navegação:
 * Se estiver no Dashboard, vai para instituicoes.php com o filtro na URL.
 * Se já estiver em instituicoes.php, apenas aplica o filtro na tabela.
 */
function navegarFiltro(tipo) {
    const paginaAtual = window.location.pathname;
    
    if (paginaAtual.includes('dashboard.php')) {
        // Vai para a listagem passando o filtro
        window.location.href = `instituicoes.php?filtro=${tipo}`;
    } else {
        // Se já estiver na página de listagem, chama a função de filtro global
        if (typeof aplicarFiltro === 'function') {
            aplicarFiltro(tipo);
        }
    }
}
</script>