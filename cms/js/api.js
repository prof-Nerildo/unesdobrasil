const API_URL = "http://localhost/unesdobrasil/saas/index.php/api";

async function chamarApi(endpoint, metodo = 'GET', dados = null) {
    // 1. Pega o token salvo no navegador
    const token = localStorage.getItem('token_unes');
    
    const configuracao = {
        method: metodo,
        headers: {
            'Content-Type': 'application/json'
        }
    };

    // 2. Se tiver token, anexa no cabeçalho Authorization
    if (token) {
        configuracao.headers['Authorization'] = `Bearer ${token}`;
    }

    // 3. Se houver dados (POST/PUT), converte para string JSON
    if (dados) {
        configuracao.body = JSON.stringify(dados);
    }

    try {
        const response = await fetch(`${API_URL}${endpoint}`, configuracao);
        
        // 4. Verifica se a resposta é um JSON válido antes de converter
        const contentType = response.headers.get("content-type");
        let resultado;

        if (contentType && contentType.indexOf("application/json") !== -1) {
            resultado = await response.json();
        } else {
            // Se o PHP der erro fatal (texto puro), capturamos aqui
            const textoErro = await response.text();
            console.error("Resposta não é JSON:", textoErro);
            return { erro: true, message: "Erro interno no servidor (PHP)." };
        }

        // 5. Tratamento de permissão e sessão
        if (response.status === 401 || response.status === 403) {
            // Se não for a própria tela de login, manda o usuário logar
            if (!window.location.href.includes('login.html')) {
                alert("Sessão expirada ou Sem permissão!");
                localStorage.removeItem('token_unes'); // Limpa o token podre
                window.location.href = 'login.html';
            }
        }

        return resultado;

    } catch (error) {
        console.error("Erro na chamada da API:", error);
        return { erro: true, message: "Erro de conexão com o servidor. Verifique o Laragon/XAMPP." };
    }
}