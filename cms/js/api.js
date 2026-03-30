const API_URL = "http://localhost/unesdobrasil/saas/index.php/api";

async function chamarApi(endpoint, metodo = 'GET', dados = null) {
    const token = localStorage.getItem('token_unes');
    
    const configuracao = {
        method: metodo,
        headers: {
            'Content-Type': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
        }
    };

    if (dados) {
        configuracao.body = JSON.stringify(dados);
    }

    try {
        const response = await fetch(`${API_URL}${endpoint}`, configuracao);
        const resultado = await response.json();
        
        if (response.status === 401 || response.status === 403) {
            alert("Sessão expirada ou Sem permissão!");
            // window.location.href = 'login.html';
        }

        return resultado;
    } catch (error) {
        console.error("Erro na chamada da API:", error);
        return { erro: true, message: "Erro de conexão com o servidor." };
    }
}