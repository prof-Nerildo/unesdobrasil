<?php include_once '../includes/headerUnes.php'; ?>
<?php include_once '../includes/sidebarUnes.php'; ?>

<style>
    /* Badges de status e ACL */
    .badge-acl {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-unes { background: #ebf5fb; color: #2980b9; }
    .badge-ativo { background: #eafaf1; color: #27ae60; }
    .badge-suspenso { background: #fdedec; color: #e74c3c; }
    
    /* Botões de ação na tabela */
    .btn-acao {
        border: none;
        background: none;
        cursor: pointer;
        padding: 6px 8px;
        border-radius: 6px;
        transition: all 0.2s ease;
        margin-right: 2px;
        font-size: 14px;
    }
    .btn-acao.edit { color: #3182ce; }
    .btn-acao.edit:hover { background: #ebf8ff; }
    .btn-acao.suspend { color: #e67e22; }
    .btn-acao.suspend:hover { background: #fef5e7; }
    .btn-acao.reactivate { color: #27ae60; }
    .btn-acao.reactivate:hover { background: #eafaf1; }

    .table-usuarios tr:hover { background: #f7fafc; transition: 0.2s; }
    .table-usuarios td { padding: 14px 12px; vertical-align: middle; }
    .table-usuarios th { padding: 12px; }

    /* ===== MODAL ===== */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.2s ease;
    }
    .modal-overlay.ativo { display: flex; }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    .modal-box {
        background: white;
        border-radius: 12px;
        padding: 30px;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        animation: slideUp 0.3s ease;
        max-height: 90vh;
        overflow-y: auto;
    }
    .modal-box h2 {
        margin: 0 0 20px;
        color: #2c3e50;
        font-size: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .modal-box h2 i { color: #3498db; }

    .campo-form {
        margin-bottom: 16px;
    }
    .campo-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 13px;
        color: #4a5568;
    }
    .campo-form input, .campo-form select {
        width: 100%;
        padding: 10px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        transition: border 0.2s;
        box-sizing: border-box;
        background: #f8fafc;
    }
    .campo-form input:focus, .campo-form select:focus {
        border-color: #3498db;
        outline: none;
        background: #fff;
    }

    .form-row {
        display: flex;
        gap: 12px;
    }
    .form-row .campo-form { flex: 1; }

    .modal-acoes {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 24px;
        padding-top: 16px;
        border-top: 1px solid #edf2f7;
    }
    .btn-modal {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-modal.cancelar {
        background: #edf2f7;
        color: #4a5568;
    }
    .btn-modal.cancelar:hover { background: #e2e8f0; }
    .btn-modal.salvar {
        background: #3498db;
        color: white;
    }
    .btn-modal.salvar:hover { background: #2980b9; }
    .btn-modal:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Toast de notificação */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 14px 24px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        font-size: 14px;
        z-index: 99999;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        animation: slideUp 0.3s ease;
        max-width: 350px;
    }
    .toast.sucesso { background: #27ae60; }
    .toast.erro { background: #e74c3c; }

    /* Status indicator */
    .status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
    }
    .status-dot.ativo { background: #27ae60; }
    .status-dot.suspenso { background: #e74c3c; }
</style>

<main class="content">
    <section class="main-section">
        <header class="top-bar">
            <h1>Gestão de Usuários</h1>
            <button class="btn-novo" onclick="abrirModalNovo()">
                <i class="fas fa-plus"></i> Novo Usuário
            </button>
        </header>

        <div class="card-painel" style="background: #fff; padding: 20px; border-radius: 8px; margin-top: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <table class="table-usuarios" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #edf2f7; color: #718096; font-size: 13px;">
                        <th style="padding: 12px;">NOME COMPLETO</th>
                        <th>E-MAIL</th>
                        <th>CARGO</th>
                        <th>STATUS</th>
                        <th style="text-align: center;">AÇÕES</th>
                    </tr>
                </thead>
                <tbody id="lista-usuarios">
                </tbody>
            </table>
        </div>
    </section>

    <?php include_once '../includes/footer.php'; ?>
</main>

<!-- Modal de Criar / Editar Usuário -->
<div class="modal-overlay" id="modalUsuario">
    <div class="modal-box">
        <h2 id="modalTitulo"><i class="fas fa-user-plus"></i> <span>Novo Usuário UNES</span></h2>
        <input type="hidden" id="editando_id" value="">

        <div class="form-row">
            <div class="campo-form">
                <label>Nome *</label>
                <input type="text" id="campo_nome" placeholder="Primeiro nome">
            </div>
            <div class="campo-form">
                <label>Sobrenome</label>
                <input type="text" id="campo_sobrenome" placeholder="Sobrenome">
            </div>
        </div>

        <div class="campo-form">
            <label>E-mail *</label>
            <input type="email" id="campo_email" placeholder="email@exemplo.com">
        </div>

        <div class="form-row">
            <div class="campo-form">
                <label>Usuário (login) *</label>
                <input type="text" id="campo_username" placeholder="Nome de usuário">
            </div>
            <div class="campo-form">
                <label>Cargo</label>
                <input type="text" id="campo_cargo" placeholder="Ex: Gestor, Analista">
            </div>
        </div>

        <div class="form-row">
            <div class="campo-form">
                <label id="label_senha">Senha *</label>
                <input type="password" id="campo_senha" placeholder="Mínimo 6 caracteres">
            </div>
            <div class="campo-form">
                <label>Perfil</label>
                <select id="campo_perfil">
                    <option value="2">Administrador UNES</option>
                    <option value="3" selected>Colaborador UNES</option>
                </select>
            </div>
        </div>

        <div class="modal-acoes">
            <button class="btn-modal cancelar" onclick="fecharModal()">Cancelar</button>
            <button class="btn-modal salvar" id="btnSalvar" onclick="salvarUsuario()">
                <i class="fas fa-save"></i> Salvar
            </button>
        </div>
    </div>
</div>

<script>
// ===================== CARREGAR LISTA =====================
async function carregarUsuarios() {
    const lista = document.getElementById('lista-usuarios');
    const token = localStorage.getItem('token_unes');

    if (!token) {
        lista.innerHTML = '<tr><td colspan="5" style="padding:20px; text-align:center; color:red;">Usuário não autenticado. Faça login novamente.</td></tr>';
        return;
    }

    try {
        lista.innerHTML = '<tr><td colspan="5" style="padding:20px; text-align:center;"><i class="fas fa-spinner fa-spin"></i> Carregando usuários...</td></tr>';

        const res = await chamarApi('/usuarios/todos');

        if (res.erro) {
            lista.innerHTML = `<tr><td colspan="5" style="padding:20px; text-align:center; color:orange;">${res.message}</td></tr>`;
            return;
        }

        const usuarios = res.dados;

        if (!usuarios || usuarios.length === 0) {
            lista.innerHTML = '<tr><td colspan="5" style="padding:20px; text-align:center;">Nenhum usuário UNES cadastrado.</td></tr>';
            return;
        }

        const html = usuarios.map(u => {
            const isSuspenso = parseInt(u.idStatus) === 4;
            const statusClass = isSuspenso ? 'suspenso' : 'ativo';
            const statusLabel = isSuspenso ? 'Suspenso' : 'Ativo';
            
            // Botão de ação de status (suspender ou reativar)
            const btnStatus = isSuspenso 
                ? `<button onclick="alterarStatus(${u.id}, 2)" class="btn-acao reactivate" title="Reativar Usuário"><i class="fas fa-user-check"></i></button>`
                : `<button onclick="alterarStatus(${u.id}, 4)" class="btn-acao suspend" title="Suspender Usuário"><i class="fas fa-user-slash"></i></button>`;

            return `
            <tr style="border-bottom: 1px solid #edf2f7; ${isSuspenso ? 'opacity: 0.6;' : ''}">
                <td style="font-weight: 500;">${u.primeiro_nome} ${u.sobrenome || ''}</td>
                <td style="color: #4a5568;">${u.email}</td>
                <td style="color: #718096; font-size: 13px;">${u.cargo || '—'}</td>
                <td>
                    <span class="status-dot ${statusClass}"></span>
                    <span class="badge-acl badge-${statusClass}">${statusLabel}</span>
                </td>
                <td style="text-align: center;">
                    <button onclick="prepararEdicao(${u.id})" class="btn-acao edit" title="Editar Usuário"><i class="fas fa-user-edit"></i></button>
                    ${btnStatus}
                </td>
            </tr>`;
        }).join('');

        lista.innerHTML = html;

    } catch (e) {
        console.error("Erro ao carregar lista de usuários:", e);
        lista.innerHTML = '<tr><td colspan="5" style="padding:20px; text-align:center; color:red;">Erro ao conectar com o servidor.</td></tr>';
    }
}

// ===================== MODAL =====================
function abrirModalNovo() {
    document.getElementById('editando_id').value = '';
    document.getElementById('campo_nome').value = '';
    document.getElementById('campo_sobrenome').value = '';
    document.getElementById('campo_email').value = '';
    document.getElementById('campo_username').value = '';
    document.getElementById('campo_cargo').value = '';
    document.getElementById('campo_senha').value = '';
    document.getElementById('campo_perfil').value = '3';
    
    document.getElementById('campo_email').removeAttribute('readonly');
    document.getElementById('campo_username').removeAttribute('readonly');
    
    document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-user-plus"></i> <span>Novo Usuário UNES</span>';
    document.getElementById('label_senha').textContent = 'Senha *';
    document.getElementById('campo_senha').setAttribute('placeholder', 'Mínimo 6 caracteres');
    document.getElementById('modalUsuario').classList.add('ativo');
}

async function prepararEdicao(id) {
    try {
        const res = await chamarApi(`/usuarios/buscar/${id}`);
        if (res.erro) {
            toast(res.message, 'erro');
            return;
        }
        const u = res.dados;

        document.getElementById('editando_id').value = u.id;
        document.getElementById('campo_nome').value = u.primeiro_nome || '';
        document.getElementById('campo_sobrenome').value = u.sobrenome || '';
        document.getElementById('campo_email').value = u.email || '';
        document.getElementById('campo_username').value = u.username || '';
        document.getElementById('campo_cargo').value = u.cargo || '';
        document.getElementById('campo_senha').value = '';
        document.getElementById('campo_perfil').value = u.idPerfil || '3';

        // E-mail e username não podem ser mudados na edição (identificadores)
        document.getElementById('campo_email').setAttribute('readonly', true);
        document.getElementById('campo_username').setAttribute('readonly', true);

        document.getElementById('modalTitulo').innerHTML = '<i class="fas fa-user-edit"></i> <span>Editar Usuário</span>';
        document.getElementById('label_senha').textContent = 'Nova Senha (deixe vazio para manter)';
        document.getElementById('campo_senha').setAttribute('placeholder', 'Deixe vazio para não alterar');
        document.getElementById('modalUsuario').classList.add('ativo');
    } catch (e) {
        console.error(e);
        toast('Erro ao buscar dados do usuário.', 'erro');
    }
}

function fecharModal() {
    document.getElementById('modalUsuario').classList.remove('ativo');
}

// Fecha modal ao clicar fora
document.getElementById('modalUsuario').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});

// ===================== SALVAR (CRIAR OU EDITAR) =====================
async function salvarUsuario() {
    const id = document.getElementById('editando_id').value;
    const btn = document.getElementById('btnSalvar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

    const dados = {
        primeiro_nome: document.getElementById('campo_nome').value.trim(),
        sobrenome: document.getElementById('campo_sobrenome').value.trim(),
        email: document.getElementById('campo_email').value.trim(),
        username: document.getElementById('campo_username').value.trim(),
        cargo: document.getElementById('campo_cargo').value.trim(),
        idPerfil: parseInt(document.getElementById('campo_perfil').value)
    };

    const senha = document.getElementById('campo_senha').value.trim();

    // Validação do frontend
    if (!dados.primeiro_nome || !dados.email || !dados.username) {
        toast('Preencha os campos obrigatórios: Nome, E-mail e Usuário.', 'erro');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Salvar';
        return;
    }

    if (!id && (!senha || senha.length < 6)) {
        toast('A senha é obrigatória e deve ter no mínimo 6 caracteres.', 'erro');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Salvar';
        return;
    }

    // Adiciona senha se preenchida
    if (senha) {
        dados.senha = senha;
    }

    try {
        let res;
        if (id) {
            // EDIÇÃO
            res = await chamarApi(`/usuarios/atualizar/${id}`, 'PUT', dados);
        } else {
            // CRIAÇÃO
            res = await chamarApi('/usuarios/criar', 'POST', dados);
        }

        if (res.erro) {
            toast(res.message, 'erro');
        } else {
            toast(res.message, 'sucesso');
            fecharModal();
            carregarUsuarios(); // Recarrega a tabela
        }
    } catch (e) {
        console.error(e);
        toast('Erro ao salvar usuário.', 'erro');
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save"></i> Salvar';
}

// ===================== SUSPENDER / REATIVAR =====================
async function alterarStatus(id, novoStatus) {
    const acao = novoStatus === 4 ? 'suspender' : 'reativar';
    if (!confirm(`Deseja realmente ${acao} este usuário?`)) return;

    try {
        const res = await chamarApi(`/usuarios/suspender/${id}`, 'PUT', { idStatus: novoStatus });

        if (res.erro) {
            toast(res.message, 'erro');
        } else {
            toast(res.message, 'sucesso');
            carregarUsuarios();
        }
    } catch (e) {
        console.error(e);
        toast('Erro ao alterar status do usuário.', 'erro');
    }
}

// ===================== TOAST DE NOTIFICAÇÃO =====================
function toast(mensagem, tipo = 'sucesso') {
    const el = document.createElement('div');
    el.className = `toast ${tipo}`;
    el.innerHTML = `<i class="fas fa-${tipo === 'sucesso' ? 'check-circle' : 'exclamation-circle'}"></i> ${mensagem}`;
    document.body.appendChild(el);
    setTimeout(() => {
        el.style.opacity = '0';
        el.style.transition = 'opacity 0.4s';
        setTimeout(() => el.remove(), 400);
    }, 3500);
}

// ===================== INICIALIZAÇÃO =====================
window.onload = carregarUsuarios;
</script>

<?php include_once '../includes/footerUnes-2.php'; ?>