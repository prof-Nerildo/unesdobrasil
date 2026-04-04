<?php include_once '../includes/headerUnes.php'; ?>
<?php include_once '../includes/sidebaUnes.php'; ?>

<main class="content">
    <section class="main-section">
        <header class="top-bar">
            <h1>Gestão de Instituições</h1>
            <button class="btn-novo" onclick="window.location.href='cadastro-instituicao.html'">
                <i class="fas fa-plus"></i> Nova Instituição
            </button>
        </header>

        <div class="container-fluid" style="padding: 20px;">
            <?php include '../componentes/cards_instituicoes.php'; ?>
        </div>

        <div class="search-container" style="margin: 0 20px 20px 20px;">
            <input type="text" id="inputBusca" placeholder="🔍 Buscar por nome, cidade ou responsável..." onkeyup="filtrarPorTexto()">
        </div>

        <div class="card-tabela">
            <table width="100%">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cidade</th>
                        <th>Nome Fantasia</th>
                        <th>Responsável</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        <th style="text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody id="corpoTabela">
                    <tr><td colspan="7" style="text-align:center;">Carregando dados...</td></tr>
                </tbody>
            </table>
        </div>
        
        <div id="paginacao" class="paginacao-container"></div>
    </section>

    <?php include_once '../includes/footer.php'; ?>
</main>

<style>
    .card-tabela { background: white; margin: 0 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; }
    table { border-collapse: collapse; width: 100%; }
    table thead tr { background: #f8f9fa; border-bottom: 2px solid #dee2e6; }
    table th { padding: 15px; text-align: left; color: #495057; font-size: 13px; text-transform: uppercase; }
    table td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; color: #333; }
    table tr:hover { background-color: #fcfcfc; }
    
    .btn-acao { background: none; border: none; cursor: pointer; font-size: 16px; padding: 5px; margin: 0 5px; transition: 0.2s; }
    .btn-acao:hover { transform: scale(1.2); }
    .btn-edit { color: #ffc107; }
    .btn-delete { color: #dc3545; }

    /* Estilo da Busca */
    .search-container input {
        width: 100%;
        max-width: 400px;
        padding: 12px 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
        transition: 0.3s;
    }
    .search-container input:focus { border-color: #2c3e50; box-shadow: 0 0 8px rgba(44, 62, 80, 0.1); }

    /* Estilo da Paginação */
    .paginacao-container { display: flex; justify-content: center; gap: 5px; margin: 20px 0; min-height: 40px; }
    .btn-pag { padding: 8px 14px; border: 1px solid #dee2e6; background: white; cursor: pointer; border-radius: 4px; transition: 0.3s; }
    .btn-pag:hover { background: #f8f9fa; }
    .btn-pag.active { background: #2c3e50; color: white; border-color: #2c3e50; }
</style>

<script>
    let todasInstituicoes = []; // Base de dados bruta
    let listaFiltrada = [];    // Base de dados após filtros de cards e busca
    let paginaAtual = 1;
    const itensPorPagina = 10;
    let filtroAtivo = 'todos'; // Guarda qual card está selecionado

    async function inicializarPagina() {
        const corpo = document.getElementById('corpoTabela');
        try {
            // Chama a função global e guarda o resultado na nossa variável local
            todasInstituicoes = await atualizarCards(); 
            
            const urlParams = new URLSearchParams(window.location.search);
            filtroAtivo = urlParams.get('filtro') || 'todos';

            aplicarFiltro(filtroAtivo); 
            
        } catch (error) {
            console.error(error);
            corpo.innerHTML = "<tr><td colspan='7' style='text-align:center;'>Erro ao carregar dados.</td></tr>";
        }
    }

    // Filtro dos Cards (Status/Catraca)
    function aplicarFiltro(tipo) {
        filtroAtivo = tipo;
        
        if (tipo === '3') {
            listaFiltrada = todasInstituicoes.filter(i => parseInt(i.idStatus) === 3);
        } else if (tipo === 'nao') {
            listaFiltrada = todasInstituicoes.filter(i => parseInt(i.idStatus) === 2 && (i.usa_catraca === 'nao' || !i.usa_catraca));
        } else if (tipo === 'sim') {
            listaFiltrada = todasInstituicoes.filter(i => parseInt(i.idStatus) === 2 && i.usa_catraca === 'sim');
        } else {
            listaFiltrada = todasInstituicoes;
        }
        
        paginaAtual = 1;
        renderizarTabela(); // Atualiza a tabela na tela
    }
    // Filtro de Busca (Texto)
    function filtrarPorTexto() {
        const termo = document.getElementById('inputBusca').value.toLowerCase();
        
        // Primeiro aplica o filtro do card selecionado
        let baseParaBusca = [];
        if (filtroAtivo === '3') {
            baseParaBusca = todasInstituicoes.filter(i => parseInt(i.idStatus) === 3);
        } else if (filtroAtivo === 'nao') {
            baseParaBusca = todasInstituicoes.filter(i => parseInt(i.idStatus) === 2 && i.usa_catraca === 'nao');
        } else if (filtroAtivo === 'sim') {
            baseParaBusca = todasInstituicoes.filter(i => parseInt(i.idStatus) === 2 && i.usa_catraca === 'sim');
        } else {
            baseParaBusca = todasInstituicoes;
        }

        // Depois filtra pelo texto digitado
        listaFiltrada = baseParaBusca.filter(inst => {
            const nome = (inst.nome_fantasia || "").toLowerCase();
            const cidade = (inst.cidade || "").toLowerCase();
            const responsavel = (inst.responsavel || "").toLowerCase();
            return nome.includes(termo) || cidade.includes(termo) || responsavel.includes(termo);
        });

        paginaAtual = 1;
        renderizarTabela();
    }

    function renderizarTabela() {
        const corpo = document.getElementById('corpoTabela');
        corpo.innerHTML = "";

        const inicio = (paginaAtual - 1) * itensPorPagina;
        const fim = inicio + itensPorPagina;
        const itensExibidos = listaFiltrada.slice(inicio, fim);

        if (itensExibidos.length === 0) {
            corpo.innerHTML = "<tr><td colspan='7' style='text-align:center;'>Nenhum registro encontrado.</td></tr>";
            document.getElementById('paginacao').innerHTML = "";
            return;
        }

        itensExibidos.forEach(inst => {
            corpo.innerHTML += `
                <tr>
                    <td>#${inst.idInstituicao}</td>
                    <td>${inst.cidade || '---'}</td>
                    <td><b>${inst.nome_fantasia}</b></td>
                    <td>${inst.responsavel || '---'}</td>
                    <td>${inst.telefone || '---'}</td>
                    <td style="color: #007bff;">${inst.email_usuario || '---'}</td>
                    <td style="text-align: center;">
                        <button class="btn-acao btn-edit" onclick="abrirEdicao(${inst.idInstituicao})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-acao btn-delete" onclick="deletarInstituicao(${inst.idInstituicao})" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
        });

        montarPaginador();
    }

    function montarPaginador() {
        const totalPaginas = Math.ceil(listaFiltrada.length / itensPorPagina);
        const container = document.getElementById('paginacao');
        container.innerHTML = "";

        if (totalPaginas <= 1) return;

        for (let i = 1; i <= totalPaginas; i++) {
            const btn = document.createElement('button');
            btn.innerText = i;
            btn.className = `btn-pag ${i === paginaAtual ? 'active' : ''}`;
            btn.onclick = () => {
                paginaAtual = i;
                renderizarTabela();
                window.scrollTo(0,0);
            };
            container.appendChild(btn);
        }
    }

    function abrirEdicao(id) {
        localStorage.setItem('edit_id_instituicao', id);
        // Verifique se o arquivo realmente se chama .html ou se você mudou para .php
        window.location.href = 'edita-instituicao.php'; 
    }

    async function deletarInstituicao(id) {
        if (confirm("Deseja realmente remover esta instituição?")) {
            const res = await chamarApi('/instituicao/deletar/' + id, 'DELETE');
            if (!res.erro) inicializarPagina();
        }
    }

    async function deletarInstituicao(id) {
        if (confirm("Deseja realmente desativar esta instituição?")) {
            // Chamada usando a rota que criamos no index.php
            const res = await chamarApi(`/instituicao/status/${id}`, 'PUT', { idStatus: 1 });
            
            if (!res.erro) {
                alert("Instituição desativada!");
                inicializarPagina(); 
            } else {
                alert(res.message);
            }
        }
    }

inicializarPagina();
</script>

<?php include_once '../includes/footerUnes-2.php'; ?>