</section> </main> </div> 
<script>
    function logout() {
        if(confirm("Deseja realmente sair do sistema?")) {
            localStorage.removeItem('token_unes');
            window.location.href = '../login.html';
        }
    }

    const formatarMoeda = (valor) => {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor);
    };
</script>
</body>
</html>