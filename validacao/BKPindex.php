<?php
$id_carteirinha = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validação UNES - BR</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Estilo Geral (Fundo Cinza) */
        body { 
            font-family: 'Helvetica', Arial, sans-serif; 
            background-color: #e5e7eb; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
        }

        /* Container do Cartão (Interface Limpa) */
        .card-estudantil { 
            width: 100%; 
            max-width: 380px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); /* Sombra mais suave */
            overflow: hidden;
            position: relative;
        }

        /* Cabeçalho com Logos */
        .header-logos {
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 20px 10px;
        }
        .logo-dne { width: 80px; }
        .logo-mapa { width: 60px; }

        /* Área da Foto e QR Code */
        .photo-section {
            display: flex;
            justify-content: space-between;
            padding: 0 25px;
            margin-bottom: 20px;
        }
        .foto-aluno {
            width: 130px;
            height: 160px;
            object-fit: cover;
            border-radius: 4px;
        }
        .qr-side {
            text-align: center;
            background: white;
            padding: 5px;
            border-radius: 4px;
        }
        .qr-code { width: 110px; height: 110px; }
        .id-label { font-size: 14px; font-weight: bold; margin-top: 5px; color: #333; }

        /* Bloco Branco de Dados */
        .info-box {
            background: white;
            margin: 0 15px 20px 15px;
            padding: 25px;
            border-radius: 10px;
            text-align: left;
        }

        .nome-aluno { font-size: 20px; font-weight: 900; color: #000; margin-bottom: 15px; }
        
        .label-group { margin-bottom: 12px; }
        .label-title { display: block; font-size: 13px; color: #666; }
        .label-value { display: block; font-size: 16px; font-weight: 800; color: #000; }

        /* Linhas de CPF/RG/NASC (Flex para economizar espaço) */
        .row-inline { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .row-inline span:first-child { color: #666; font-size: 14px; font-weight: bold; }
        .row-inline span:last-child { color: #000; font-size: 14px; font-weight: 900; }

        .validade-box {
            text-align: center;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .validade-label { font-size: 12px; font-weight: bold; color: #000; }
        .validade-data { font-size: 16px; font-weight: 900; color: #000; }

        /* Loader e Busca */
        #loader { padding: 50px; text-align: center; color: #3182ce; display: none; }
        #sessao-busca { padding: 40px 20px; text-align: center; background: white; border-radius: 20px; }
        input { width: 80%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 8px; }
        .btn-validar { background: #3182ce; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<div class="card-estudantil">
    
    <div id="sessao-busca" <?php if($id_carteirinha) echo 'style="display:none;"'; ?>>
        <h2 style="color:#2c3e50">Validação UNES</h2>
        <input type="text" id="codigoInput" placeholder="Código de Uso" value="<?php echo htmlspecialchars($id_carteirinha ?? ''); ?>">
        <button class="btn-validar" onclick="buscarDados()">VALIDAR AGORA</button>
    </div>

    <div id="loader">
        <i class="fas fa-spinner fa-spin fa-3x"></i>
        <p>Verificando autenticidade...</p>
    </div>

    <div id="resultado" style="display: none;">
        <div class="header-logos">
            <img src="../img/logo-dne-blue.png" class="logo-dne" alt="Logo DNE"> 
            <img src="../img/mapa-unes.png" class="logo-mapa" alt="Logo UNES">
            <img src="../img/mapa-br.png" class="logo-mapa" alt="Mapa Brasil">
        </div>

        <div class="photo-section">
            <img id="foto" src="" class="foto-aluno" alt="Foto do Aluno">
            <div class="qr-side">
                <img id="qrcode-img" src="" class="qr-code" alt="QR Code">
                <div id="res-idcard" class="id-label"></div>
            </div>
        </div>

        <div class="info-box">
            <div id="res-nome" class="nome-aluno"></div>

            <div class="label-group">
                <span class="label-title">Instituição de Ensino</span>
                <span id="res-inst" class="label-value"></span>
            </div>

            <div class="label-group">
                <span class="label-title">Série/Curso</span>
                <span id="res-curso" class="label-value"></span>
            </div>

            <div class="row-inline"><span>CPF</span><span id="res-cpf"></span></div>
            <div class="row-inline"><span>RG/Identidade</span><span id="res-rg"></span></div>
            <div class="row-inline"><span>Data de Nasc.</span><span id="res-nasc"></span></div>

            <div class="validade-box">
                <div class="validade-label">VALIDADE</div>
                <div class="validade-data">MARÇO / 2027</div>
            </div>
        </div>
        <button onclick="location.href='index.php'" style="width:100%; border:none; padding:15px; background:#f8fafc; cursor:pointer; font-weight:bold; color:#666;">NOVA CONSULTA</button>
    </div>
</div>

<script>
    async function buscarDados() {
        const codigo = document.getElementById('codigoInput').value.trim();
        if (!codigo) return;

        document.getElementById('sessao-busca').style.display = 'none';
        document.getElementById('loader').style.display = 'block';

        try {
            const configRes = await axios.get('../saas/appsettings.json');
            const config = configRes.data;
            const ambiente = config.Ambiente;
            const apiUrl = config.Ambientes[ambiente].ApiUrl;
            const siteUrl = config.Ambientes[ambiente].SiteUrl;

            const response = await axios.get(`${apiUrl}/documento/validar-publico/${codigo}`);
            
            if(!response.data.erro) {
                const d = response.data.dados;
                
                document.getElementById('res-nome').innerText = d.NomeDocumento;
                document.getElementById('res-inst').innerText = d.InsEnsinoDocumento;
                document.getElementById('res-curso').innerText = d.serieDocumento;
                document.getElementById('res-cpf').innerText = d.nCPF;
                document.getElementById('res-rg').innerText = d.nRGDocumento || '--';
                document.getElementById('res-idcard').innerText = d.idCard;
                
                // Formata Data de Nascimento
                const nasc = d.dataNascDocumento.split('-').reverse().join('/');
                document.getElementById('res-nasc').innerText = nasc;

                // Foto e QR Code
                document.getElementById('foto').src = `${siteUrl}../${d.fotoDocumento}`;
                document.getElementById('qrcode-img').src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${window.location.href}`;
                
                document.getElementById('loader').style.display = 'none';
                document.getElementById('resultado').style.display = 'block';
            } else {
                alert("Documento Inválido");
                location.href='index.php';
            }
        } catch (error) {
            alert("Erro de conexão");
            location.href='index.php';
        }
    }

    window.onload = () => {
        if (document.getElementById('codigoInput').value.length > 5) buscarDados();
    };
</script>
</body>
</html>