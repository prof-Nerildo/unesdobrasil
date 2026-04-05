<?php 
// Aqui você pode incluir o header e sidebar que já usa, 
// ou criar um específico para a instituição se o menu for diferente.
include '../includes/headerUnes.php'; 
include '../includes/sidebarUnes.php'; 
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <h2 class="text-dark">Painel da Instituição</h2>
                <p class="text-muted" id="nomeFantasiaEscola">Carregando dados da escola...</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow border-0 text-white bg-primary mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Alunos Cadastrados</h5>
                                <h2 id="totalAlunos">0</h2>
                            </div>
                            <i class="fas fa-user-graduate fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow border-0 text-white bg-warning mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Pendentes de Aprovação</h5>
                                <h2 id="totalPendentes">0</h2>
                            </div>
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow border-0 text-white bg-success mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Documentos Emitidos</h5>
                                <h2 id="totalEmitidos">0</h2>
                            </div>
                            <i class="fas fa-id-card fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow border-0">
                    <div class="card-header bg-white font-weight-bold">
                        Últimas Solicitações de Alunos
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Curso</th>
                                    <th>Data Solicitação</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="listaRecentes">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/api.js"></script>
<script>
    async function inicializarDashboard() {
        const user = obterUsuario(); // Função que já temos no api.js
        if (!user || !user.idInstituicao) {
            window.location.href = '../login.html';
            return;
        }

        // Exibe o nome da escola no topo
        document.getElementById('nomeFantasiaEscola').innerText = user.nome_fantasia || "Gestão Institucional";

        // Aqui chamaremos os dados reais (precisaremos criar esse endpoint no PHP)
        const res = await chamarApi(`/instituicao/dashboard-institucional/${user.idInstituicao}`);
        
        if (!res.erro) {
            document.getElementById('totalAlunos').innerText = res.dados.totalAlunos;
            document.getElementById('totalPendentes').innerText = res.dados.totalPendentes;
            document.getElementById('totalEmitidos').innerText = res.dados.totalEmitidos;
            
            // Lógica para preencher a tabela de recentes se houver dados
        }
    }

    document.addEventListener('DOMContentLoaded', inicializarDashboard);
</script>

<?php include '../includes/footerUnes-2.php'; ?>