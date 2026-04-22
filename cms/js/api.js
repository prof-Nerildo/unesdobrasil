// 1. Proteção contra redeclaração
if (typeof API_URL === 'undefined') {
    //  Local
    var API_URL = "http://localhost/unesdobrasil/saas/index.php/api";
    // web teste
    //var API_URL = "https://www.webdna.com.br/unes/saas/index.php/api";
}

async function chamarApi(endpoint, metodo = 'GET', dados = null) {
    const token = localStorage.getItem('token_unes');
    
    const configuracao = {
        method: metodo,
        headers: {
            'Content-Type': 'application/json'
        }
    };

    if (token) {
        configuracao.headers['Authorization'] = `Bearer ${token}`;
    }

    if (dados && (metodo === 'POST' || metodo === 'PUT')) {
        configuracao.body = JSON.stringify(dados);
    }

    try {
        const response = await fetch(`${API_URL}${endpoint}`, configuracao);
        const contentType = response.headers.get("content-type");
        let resultado;

        if (contentType && contentType.indexOf("application/json") !== -1) {
            resultado = await response.json();
        } else {
            const textoErro = await response.text();
            console.error("Resposta do servidor não é JSON:", textoErro);
            return { erro: true, message: "Erro interno no servidor PHP." };
        }

        // --- NOVO: LÓGICA DE ACL E PERMISSÃO ---
        if (response.status === 401 || response.status === 403) {
            const paginaAtual = window.location.pathname;
            if (!paginaAtual.endsWith('../login.html')) {
                localStorage.removeItem('token_unes');
                localStorage.removeItem('user_unes'); // Limpa dados do usuário também
                window.location.replace('../login.html');
                return { erro: true, message: "Acesso negado ou sessão expirada." };
            }
        }

        return resultado;

    } catch (error) {
        console.error("Erro na chamada da API:", error);
        return { erro: true, message: "Erro de conexão. Verifique o servidor." };
    }
}

// --- FUNÇÕES DE GESTÃO DE PERFIL E ACL (ADICIONE AQUI) ---

/**
 * Salva os dados do usuário logado (idAcl, nome, etc)
 */
function guardarUsuario(dados) {
    localStorage.setItem('user_unes', JSON.stringify(dados));
}

/**
 * Retorna os dados do usuário ou nulo se não logado
 */
function obterUsuario() {
    const user = localStorage.getItem('user_unes');
    return user ? JSON.parse(user) : null;
}

/**
 * Verifica se o usuário tem a ACL necessária
 * Ex: eAdministrador(2) -> Verifica se é da UNES
 */
function temAcesso(idAclRequerido) {
    const user = obterUsuario();
    if (!user) return false;
    
    // Master (1) sempre tem acesso a tudo
    if (user.idAcl == 1) return true;
    
    return user.idAcl == idAclRequerido;
}

async function atualizarCards() {
    try {
        const res = await chamarApi('/instituicao/todas');
        const lista = Array.isArray(res) ? res : (res.dados || []);
        
        // Regras de contagem
        const qtdValidar = lista.filter(i => parseInt(i.idStatus) === 3).length;
        const qtdSemCatraca = lista.filter(i => parseInt(i.idStatus) === 2 && (i.usa_catraca === 'nao' || !i.usa_catraca)).length;
        const qtdComCatraca = lista.filter(i => parseInt(i.idStatus) === 2 && i.usa_catraca === 'sim').length;

        // Preenche os cards se eles existirem na página atual
        if(document.getElementById('qtdValidar')) document.getElementById('qtdValidar').innerText = qtdValidar;
        if(document.getElementById('qtdSemCatraca')) document.getElementById('qtdSemCatraca').innerText = qtdSemCatraca;
        if(document.getElementById('qtdComCatraca')) document.getElementById('qtdComCatraca').innerText = qtdComCatraca;

        // Se estivermos na página de gestão, devolvemos a lista para a tabela
        return lista;

    } catch (error) {
        console.error("Erro ao processar cards:", error);
        return [];
    }
}
// IMPORTANTE: Remova qualquer chamada direta de atualizarCards() aqui no final do api.js

/**
 * Faz o logout limpando tudo
 */
function logout() {
    if(confirm("Deseja realmente sair?")) {
        localStorage.clear();
        window.location.href = '../login.html';
    }
}