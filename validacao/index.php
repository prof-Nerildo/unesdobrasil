<?php
$id_carteirinha = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Validação UNES - BR</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Reset e Box-Sizing Global (Crucial para Responsividade) */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Estilo Geral (Fundo Cinza Externo) */
        body { 
            font-family: 'Helvetica', Arial, sans-serif; 
            background-color: #d1d5db; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            padding: 20px 10px; /* Respiro lateral e vertical para telas pequenas */
        }

        /* Container do Cartão (Vetor de Fundo e Responsivo) */
        .card-estudantil { 
            background: url('../img/vetor-fundo.png'); 
            background-size: cover;
            background-position: center bottom;
            background-repeat: no-repeat;
            background-color: #e5e7eb; /* Fallback */
            
            width: 94%; /* Impede que encoste nas bordas do celular */
            max-width: 400px; /* Limite máximo para desktop/tablets */
            border-radius: 12px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); 
            overflow: hidden;
            position: relative;
        }

        /* Cabeçalho com Logos */
        .header-logos {
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 25px 15px 15px 15px;
        }
        .logo-dne { width: 75px; max-width: 25%; }
        .logo-mapa { width: 55px; max-width: 18%; }

        /* Área da Foto e QR Code */
        .photo-section {
            display: flex;
            justify-content: space-evenly; /* Distribui melhor em telas estreitas */
            align-items: center;
            padding: 0 15px;
            margin-bottom: 20px;
            gap: 10px;
        }
        .foto-aluno {
            width: 125px;
            height: 155px;
            object-fit: cover;
            border-radius: 6px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .qr-side {
            text-align: center;
            background: white;
            padding: 8px;
            border-radius: 6px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .qr-code { width: 100px; height: 100px; }
        .id-label { font-size: 13px; font-weight: bold; margin-top: 5px; color: #333; }

        /* Bloco Branco de Dados */
        .info-box {
            background: white;
            margin: 0 15px 20px 15px;
            padding: 25px 20px;
            border-radius: 10px;
            text-align: left;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .nome-aluno { 
            font-size: 19px; 
            font-weight: 900; 
            color: #000; 
            margin-bottom: 18px; 
            line-height: 1.2;
            word-wrap: break-word; /* Evita quebra de layout com nomes muito longos */
        }
        
        .label-group { margin-bottom: 15px; }
        .label-title { display: block; font-size: 12px; color: #666; text-transform: uppercase; }
        .label-value { display: block; font-size: 15px; font-weight: 800; color: #000; margin-top: 3px;}

        /* Linhas de CPF/RG/NASC */
        .row-inline { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 8px; 
            align-items: center;
            flex-wrap: wrap; /* Permite quebrar linha em telas ultra-pequenas */
        }
        .row-inline span:first-child { color: #666; font-size: 13px; font-weight: bold; }
        .row-inline span:last-child { color: #000; font-size: 14px; font-weight: 900; text-align: right; }

        .validade-box {
            text-align: center;
            margin-top: 25px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .validade-label { font-size: 11px; font-weight: bold; color: #666; letter-spacing: 1px; }
        .validade-data { font-size: 16px; font-weight: 900; color: #000; margin-top: 3px; }

        /* Loader e Busca */
        #loader { padding: 50px 20px; text-align: center; color: #3182ce; display: none; }
        #sessao-busca { 
            padding: 40px 20px; 
            text-align: center; 
            background: white; 
            border-radius: 10px; 
            margin: 20px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        #sessao-busca p { font-size: 14px; color: #666; margin-bottom: 10px; }
        input { 
            width: 100%; 
            padding: 14px; 
            margin: 15px 0; 
            border: 1px solid #ccc; 
            border-radius: 8px; 
            font-size: 15px;
            text-align: center;
        }
        .btn-validar { 
            width: 100%;
            background: #3182ce; 
            color: white; 
            border: none; 
            padding: 15px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: bold; 
            font-size: 15px;
        }
        
        .btn-nova-consulta { 
            width: 100%; 
            border: none; 
            padding: 18px; 
            background: #f8fafc; 
            cursor: pointer; 
            font-weight: bold; 
            color: #666; 
            transition: background 0.3s; 
            font-size: 14px;
        }
        .btn-nova-consulta:hover { background: #e2e8f0; }

        /* Ajustes específicos para telas muito pequenas (ex: iPhone SE) */
        @media (max-width: 360px) {
            .foto-aluno { width: 110px; height: 140px; }
            .qr-code { width: 90px; height: 90px; }
            .nome-aluno { font-size: 17px; }
        }
    </style>
</head>
<body>

<div class="card-estudantil">
    
    <div id="sessao-busca" <?php if($id_carteirinha) echo 'style="display:none;"'; ?>>
        <h2 style="color:#2c3e50; margin-bottom: 15px;">[UNES]</h2>
        <p>Valide a autenticidade da sua carteirinha estudantil.</p>
        <p>Digite os números do Código de uso.</p>
        <input type="text" id="codigoInput" placeholder="Código de Uso" value="<?php echo htmlspecialchars($id_carteirinha ?? ''); ?>">
        <button class="btn-validar" onclick="buscarDados()">VALIDAR AGORA</button>
    </div>

    <div id="loader">
        <i class="fas fa-spinner fa-spin fa-3x"></i>
        <p style="margin-top: 15px; font-weight: bold;">Verificando autenticidade...</p>
    </div>

    <div id="resultado" style="display: none;">
        <div class="header-logos">
            <img src="../img/logo-dne.png" class="logo-dne" alt="Logo DNE"> 
            <img src="../img/logo-unes.png" class="logo-mapa" alt="Logo UNES">
            <img src="../img/logo-br.png" class="logo-mapa" alt="Mapa Brasil">
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
        <button class="btn-nova-consulta" onclick="location.href='index.php'">NOVA CONSULTA</button>
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