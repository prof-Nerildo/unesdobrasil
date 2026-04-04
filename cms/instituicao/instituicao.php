<?php include_once 'includes/header.php'; ?>
<?php include_once 'includes/sidebar.php'; ?>

<main class="content">
    <section class="main-section">
        <header class="top-bar">
            <h1>Gestão de Instituições</h1>
            <button class="btn-novo" onclick="window.location.href='cadastro-instituicao.html'">
                <span>+</span> Nova Instituição
            </button>
        </header>

        <div class="card-tabela">
            <table width="100%">
                <thead>
                    <tr>
                        <th>Código Inst. Ensino</th>
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
    </section>

    <?php include_once 'includes/footer.php'; ?>
</main>

<style>
    .card-tabela { background: white; padding: 0; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; }
    table { border-collapse: collapse; width: 100%; }
    table thead tr { background: #f1f3f5; border-bottom: 2px solid #dee2e6; }
    table th { padding: 15px; text-align: left; color: #495057; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    table td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; color: #333; }
    table tr:hover { background-color: #f8f9fa; }
    
    .btn-acao { background: none; border: none; cursor: pointer; font-size: 18px; padding: 5px; transition: 0.2s; }
    .btn-acao:hover { opacity: 0.6; }
</style>

<script>
    async function listarInstituicoes() {
        const corpo = document.getElementById('corpoTabela');
        try {
            const res = await chamarApi('/instituicao/todas');
            
            // VEJA ISSO NO CONSOLE (F12)
            console.log("O que a API mandou:", res);

            // Se a API retornar { erro: false, dados: [...] }, precisamos pegar o .dados
            const lista = Array.isArray(res) ? res : (res.dados || []);

            if (lista.length === 0) {
                corpo.innerHTML = "<tr><td colspan='7' style='text-align:center;'>Nenhum registro encontrado no banco.</td></tr>";
                return;
            }

            corpo.innerHTML = ""; 
            lista.forEach(inst => {
                corpo.innerHTML += `
                    <tr>
                        <td>#${inst.idInstituicao}</td>
                        <td>${inst.cidade || '---'}</td>
                        <td><b>${inst.nome_fantasia}</b></td>
                        <td>${inst.responsavel || '---'}</td>
                        <td>${inst.telefone || '---'}</td>
                        <td style="color: #007bff;">${inst.email_usuario || '---'}</td>
                        <td style="text-align: center;">
                            <button class="btn-acao">📝</button>
                            <button class="btn-acao">🗑️</button>
                        </td>
                    </tr>
                `;
            });
        } catch (error) {
            console.error("Erro no JS:", error);
            corpo.innerHTML = "<tr><td colspan='7' style='text-align:center; color:red;'>Erro ao processar dados.</td></tr>";
        }
    }

    

    listarInstituicoes();
</script>

<?php include_once 'includes/footer2.php'; ?>