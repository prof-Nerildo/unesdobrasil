

// 1. Proteção contra redeclaração (O erro que apareceu no seu console F12)
if (typeof API_URL === 'undefined') {
    var API_URL = "http://localhost/unesdobrasil/saas/index.php/api";
}

async function chamarApi(endpoint, metodo = 'GET', dados = null) {
    // 2. Pega o token salvo no navegador
    const token = localStorage.getItem('token_unes');
    
    const configuracao = {
        method: metodo,
        headers: {
            'Content-Type': 'application/json'
        }
    };

    // 3. Se tiver token, anexa no cabeçalho Authorization
    if (token) {
        configuracao.headers['Authorization'] = `Bearer ${token}`;
    }

    // 4. Se houver dados (POST/PUT), converte para string JSON
    if (dados && (metodo === 'POST' || metodo === 'PUT')) {
        configuracao.body = JSON.stringify(dados);
    }

    try {
        const response = await fetch(`${API_URL}${endpoint}`, configuracao);
        
        // 5. Verifica se a resposta é um JSON válido
        const contentType = response.headers.get("content-type");
        let resultado;

        if (contentType && contentType.indexOf("application/json") !== -1) {
            resultado = await response.json();
        } else {
            const textoErro = await response.text();
            console.error("Resposta do servidor não é JSON:", textoErro);
            return { erro: true, message: "Erro interno no servidor (PHP). Verifique o banco." };
        }

        // 6. Tratamento de permissão e sessão (ERRO 401 ou 403)
        // Dentro do try do chamarApi no js/api.js
        if (response.status === 401 || response.status === 403) {
            const paginaAtual = window.location.pathname;
            
            // Se o erro for no /me e não estivermos no login, logue o erro
            console.error("ERRO DE AUTORIZAÇÃO NA PÁGINA:", paginaAtual);

            if (!paginaAtual.endsWith('login.html')) {
                // SÓ limpa e redireciona se realmente não houver token ou o servidor confirmar erro
                localStorage.removeItem('token_unes');
                window.location.replace('login.html');
                return { erro: true, message: "Sessão expirada." };
            }
        }

        return resultado;

    } catch (error) {
        console.error("Erro na chamada da API:", error);
        return { erro: true, message: "Erro de conexão. Verifique o Laragon/XAMPP." };
    }
}