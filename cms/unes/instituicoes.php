<?php include_once '../includes/headerUnes.php'; ?>
<?php include_once '../includes/sidebarUnes.php'; ?>

<main class="content">
    <section class="main-section">
        <header class="top-bar">
            <h1>Gestão de Instituições</h1>
            <button class="btn-novo" onclick="window.location.href='../cadastro-instituicao.html'">
                <i class="fas fa-plus"></i> Nova Instituição
            </button>
        </header>

        <div class="container-fluid" style="padding: 20px;">
            <?php include '../componentes/cards_instituicoes.php'; ?>
        </div>

        <div class="search-container" style="margin: 0 20px 20px 20px; display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap;">
            <input type="text" id="inputBusca" placeholder="🔍 Buscar por nome, cidade ou responsável..." onkeyup="filtrarPorTexto()" style="flex-grow: 1;">
            
            <div style="display: flex; align-items: center; gap: 10px;">
                <label for="itensPorPaginaSelect" style="font-size: 14px; font-weight: 600; color: #4a5568;">Exibir:</label>
                <select id="itensPorPaginaSelect" onchange="mudarItensPorPagina()" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none; font-size: 14px; background: white; cursor: pointer;">
                    <option value="10">10 linhas</option>
                    <option value="25">25 linhas</option>
                    <option value="50">50 linhas</option>
                    <option value="100">100 linhas</option>
                </select>
            </div>
        </div>

        <div class="card-tabela">
            <table width="100%">
                <thead>
                    <tr>
                        <th onclick="ordenarPor('idLegado')" style="cursor:pointer">
                            Código <i class="fas fa-sort" id="sort-idLegado"></i>
                        </th>
                        <th onclick="ordenarPor('cidade')" style="cursor:pointer">
                            Cidade <i class="fas fa-sort" id="sort-cidade"></i>
                        </th>
                        <th onclick="ordenarPor('nome_fantasia')" style="cursor:pointer">
                            Nome Fantasia <i class="fas fa-sort" id="sort-nome_fantasia"></i>
                        </th>
                        <th onclick="ordenarPor('responsavel')" style="cursor:pointer">
                            Responsável <i class="fas fa-sort" id="sort-responsavel"></i>
                        </th>
                        <th onclick="ordenarPor('telefone')" style="cursor:pointer">
                            Telefone <i class="fas fa-sort" id="sort-telefone"></i>
                        </th>
                        <th onclick="ordenarPor('email_usuario')" style="cursor:pointer">
                            E-mail <i class="fas fa-sort" id="sort-email_usuario"></i>
                        </th>
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

    .paginacao-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin: 30px 0;
}

.btn-pag {
    min-width: 40px;
    height: 40px;
    padding: 0 10px;
    border: 1px solid #e0e0e0;
    background: #fff;
    color: #555;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-pag:hover {
    background: #f0f0f0;
    border-color: #bbb;
}

.btn-pag.active {
    background: #2c3e50;
    color: #fff;
    border-color: #2c3e50;
    box-shadow: 0 4px 10px rgba(44, 62, 80, 0.2);
}

table th {
    transition: background 0.3s;
}
table th:hover {
    background-color: #f1f1f1;
    color: #2c3e50;
}

/* Trava a largura da coluna de Código */
table th:first-child, 
table td:first-child {
    width: 80px;
    text-align: center;
    white-space: nowrap;
}

/* Coluna de Ações: Largura fixa e botões lado a lado */
table th:last-child, 
table td:last-child {
    width: 100px;
    text-align: center;
}

.acoes-flex {
    display: flex;
    justify-content: center;
    gap: 10px; /* Espaço entre os botões */
}

/* Evita que o nome da cidade ou escola quebre em muitas linhas */
table td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px; /* Ajuste conforme necessário */
}
table th i {
    margin-left: 8px;
    font-size: 11px;
    color: #3182ce;
    transition: 0.3s;
}

table th:hover {
    background-color: #f7fafc !important;
    color: #3182ce;
}
</style>

<script>
    let todasInstituicoes = []; 
    let listaFiltrada = [];    
    let paginaAtual = 1;
    let itensPorPagina = 10;

    function mudarItensPorPagina() {
        itensPorPagina = parseInt(document.getElementById('itensPorPaginaSelect').value);
        paginaAtual = 1;
        renderizarTabela();
    }
    let filtroAtivo = 'todos'; 

    async function inicializarPagina() {
        const corpo = document.getElementById('corpoTabela');
        try {
            // 1. Carrega os dados do banco através da API
            todasInstituicoes = await atualizarCards(); 
            
            const urlParams = new URLSearchParams(window.location.search);
            filtroAtivo = urlParams.get('filtro') || 'todos';

            aplicarFiltro(filtroAtivo); 
            
        } catch (error) {
            console.error(error);
            corpo.innerHTML = "<tr><td colspan='7' style='text-align:center;'>Erro ao carregar dados.</td></tr>";
        }
    }

    function aplicarFiltro(tipo) {
        filtroAtivo = tipo;
        if (tipo === '3') {
            listaFiltrada = todasInstituicoes.filter(i => parseInt(i.idStatus) === 3);
        } else if (tipo === 'nao') {
            listaFiltrada = todasInstituicoes.filter(i => i.usa_catraca === 'nao' || !i.usa_catraca);
        } else if (tipo === 'sim') {
            listaFiltrada = todasInstituicoes.filter(i => i.usa_catraca === 'sim');
        } else {
            listaFiltrada = todasInstituicoes;
        }

        // Aplica busca por texto se houver algo digitado
        const textoBusca = document.getElementById('inputBusca').value;
        if (textoBusca.trim() !== '') {
            const termo = textoBusca.toLowerCase().trim();
            listaFiltrada = listaFiltrada.filter(i => {
                const nome = (i.nome_fantasia || '').toLowerCase();
                const cidade = (i.cidade || '').toLowerCase();
                const responsavel = (i.responsavel || '').toLowerCase();
                const email = (i.email_usuario || '').toLowerCase();
                const codigo = (i.idLegado || i.idInstituicao || '').toString();
                return nome.includes(termo) || cidade.includes(termo) || responsavel.includes(termo) || email.includes(termo) || codigo.includes(termo);
            });
        }

        paginaAtual = 1;
        renderizarTabela(); 
    }

    function filtrarPorTexto() {
        aplicarFiltro(filtroAtivo);
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
            const codigoFormatado = inst.idLegado ? inst.idLegado : inst.idInstituicao;
            corpo.innerHTML += `
                <tr>
                    <td>#${codigoFormatado}</td>
                    <td>${inst.cidade || '---'}</td>
                    <td><b>${inst.nome_fantasia}</b></td>
                    <td>${inst.responsavel || '---'}</td>
                    <td>${inst.telefone || '---'}</td>
                    <td style="color: #007bff;">${inst.email_usuario || '---'}</td>
                    <td>
                        <div class="acoes-flex">
                            <button class="btn-acao btn-edit" onclick="abrirEdicao(${inst.idInstituicao})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-acao btn-delete" onclick="deletarInstituicao(${inst.idInstituicao})" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
        });
        montarPaginador();
    }

    // --- AS FUNÇÕES QUE ESTAVAM EM FALTA E FAZEM OS BOTÕES FUNCIONAR ---

    function abrirEdicao(id) {
        localStorage.setItem('edit_id_instituicao', id);
        window.location.href = 'edita-instituicao.php'; 
    }

    async function deletarInstituicao(id) {
        if (confirm("Deseja realmente desativar esta instituição?")) {
            try {
                // 1. Avisa o banco de dados
                const res = await chamarApi(`/instituicao/status/${id}`, 'PUT', { idStatus: 1 });
                
                if (!res.erro) {
                    // 2. Remove da memória local imediatamente para ela sumir da tela
                    todasInstituicoes = todasInstituicoes.filter(inst => inst.idInstituicao !== id);
                    
                    // 3. Re-aplica o filtro e renderiza a tabela
                    aplicarFiltro(filtroAtivo);
                    
                    alert("Instituição removida com sucesso!");
                } else {
                    alert("Erro ao desativar: " + res.message);
                }
            } catch (error) {
                console.error("Erro na exclusão:", error);
                alert("Erro de conexão com o servidor.");
            }
        }
    }

    // --- PAGINAÇÃO E ORDENAÇÃO ---

   function montarPaginador() {
    const totalPaginas = Math.ceil(listaFiltrada.length / itensPorPagina);
    const container = document.getElementById('paginacao');
    container.innerHTML = "";

    if (totalPaginas <= 1) return;

    let maxBotoes = 5;
    let inicio = Math.max(1, paginaAtual - Math.floor(maxBotoes / 2));
    let fim = Math.min(totalPaginas, inicio + maxBotoes - 1);

    // Botão "PRIMEIRA" (Ícone duplo)
    if (paginaAtual > 1) {
        container.appendChild(criarBotaoPag(1, '<i class="fas fa-angle-double-left"></i>', false));
        container.appendChild(criarBotaoPag(paginaAtual - 1, '<i class="fas fa-angle-left"></i>', false));
    }

    // Números
    for (let i = inicio; i <= fim; i++) {
        container.appendChild(criarBotaoPag(i, i, i === paginaAtual));
    }

    // Botão "PRÓXIMA"
    if (paginaAtual < totalPaginas) {
        container.appendChild(criarBotaoPag(paginaAtual + 1, '<i class="fas fa-angle-right"></i>', false));
        container.appendChild(criarBotaoPag(totalPaginas, '<i class="fas fa-angle-double-right"></i>', false));
    }
}

function criarBotaoPag(pagina, html, ativo) {
    const btn = document.createElement('button');
    btn.innerHTML = html; // Usamos innerHTML para renderizar o ícone
    btn.className = `btn-pag ${ativo ? 'active' : ''}`;
    btn.onclick = () => {
        paginaAtual = pagina;
        renderizarTabela();
        window.scrollTo(0, 0);
    };
    return btn;
}

    let ordemAscendente = true;
let ultimaColunaSorteada = '';

function ordenarPor(coluna) {
    // 1. Reseta todos os ícones para o estado inicial (sort neutro)
    document.querySelectorAll('th i.fas').forEach(icon => {
        icon.className = 'fas fa-sort';
        icon.style.opacity = '0.3'; // Deixa clarinho quem não está ativo
    });

    if (ultimaColunaSorteada === coluna) {
        ordemAscendente = !ordemAscendente;
    } else {
        ordemAscendente = true;
        ultimaColunaSorteada = coluna;
    }

    // 2. Atualiza o ícone da coluna clicada
    const iconeAtivo = document.getElementById(`sort-${coluna}`);
    if (iconeAtivo) {
        iconeAtivo.className = ordemAscendente ? 'fas fa-sort-up' : 'fas fa-sort-down';
        iconeAtivo.style.opacity = '1'; // Destaca a coluna ativa
    }

    // ... (sua lógica de sort que já funciona) ...
    listaFiltrada.sort((a, b) => {
        let valA = a[coluna] ? a[coluna].toString().toLowerCase() : '';
        let valB = b[coluna] ? b[coluna].toString().toLowerCase() : '';
        if (coluna === 'idLegado') {
            valA = parseInt(a[coluna]) || 0;
            valB = parseInt(b[coluna]) || 0;
        }
        if (valA < valB) return ordemAscendente ? -1 : 1;
        if (valA > valB) return ordemAscendente ? 1 : -1;
        return 0;
    });

    paginaAtual = 1;
    renderizarTabela();
}

    inicializarPagina();
</script>

<?php include_once '../includes/footerUnes-2.php'; ?>