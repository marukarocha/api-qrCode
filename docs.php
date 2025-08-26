<?php
// Se necessário, adicione código PHP aqui

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Track Plant - Documentação</title>
    
    <!-- Material Design -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar-custom">
        <div class="nav-wrapper container">
            <a href="index.php" class="brand-logo">
                <i class="material-icons">eco</i>
                API Track Plant
            </a>
            <ul id="nav-mobile" class="right">
                <li><a href="index.php"><i class="material-icons left">home</i>Sistema</a></li>
                <li><a href="#swagger-section"><i class="material-icons left">code</i>API Docs</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Informações da API -->
        <div class="structure-info img-doc">
            <h4><i class="material-icons left">api</i>API Track Plant v3.0</h4>
            <p><strong>Sistema de QR Code para Rastreamento de Plantas</strong></p>
            <p>API RESTful para geração, gerenciamento e consulta de QR Codes otimizada para sistemas de inventário de plantas.</p>
            
            <div class="row" style="margin-top: 30px;">
                <div class="col s12 m4">
                    <div style="text-align: center;">
                        <i class="material-icons large">qr_code</i>
                        <h6>QR Codes</h6>
                        <p>Alta qualidade PNG/SVG</p>
                    </div>
                </div>
                <div class="col s12 m4">
                    <div style="text-align: center;">
                        <i class="material-icons large">devices</i>
                        <h6>Multi-plataforma</h6>
                        <p>Web, Mobile, FlutterFlow</p>
                    </div>
                </div>
                <div class="col s12 m4">
                    <div style="text-align: center;">
                        <i class="material-icons large">analytics</i>
                        <h6>Gerenciamento</h6>
                        <p>Lista, busca e controle</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Reference -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">
                            <i class="material-icons">flash_on</i>
                            Referência Rápida
                        </span>
                        
                        <div class="row">
                            <div class="col s12 m6">
                                <h6>🌐 Endpoints Principais</h6>
                                <div class="endpoint-card">
                                    <span class="method-badge method-post">POST</span>
                                    <strong>/flutter_api_v3.php</strong>
                                    <p>Gerar novo QR Code</p>
                                </div>
                                
                                <div class="endpoint-card">
                                    <span class="method-badge method-get">GET</span>
                                    <strong>/flutter_list_v3.php</strong>
                                    <p>Listar todos os QR Codes</p>
                                </div>
                                
                                <div class="endpoint-card">
                                    <span class="method-badge method-delete">POST</span>
                                    <strong>/flutter_delete_v3.php</strong>
                                    <p>Deletar QR Code específico</p>
                                </div>
                            </div>
                            
                            <div class="col s12 m6">
                                <h6>📋 Estrutura do QR ID</h6>
                                <div class="code-block">
                                    <pre>PlantId-Data-Hash

Exemplo: PLT001-20250824-abc12345

• PlantId: Identificador da planta
• Data: YYYYMMDD (20250824)  
• Hash: 8 caracteres únicos</pre>
                                </div>
                                
                                <h6>💡 Exemplo de Uso</h6>
                                <div class="code-block">
                                    <pre>curl -X POST \
  -H "Content-Type: application/json" \
  -d '{"plant_id":"PLT001","format":"png"}' \
  /flutter_api_v3.php</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Continue com o resto do conteúdo... -->
        <!-- ...existing code... -->
    </div>

    <!-- Quick Links -->
    <div class="quick-links">
        <strong>Links Rápidos</strong>
        <a href="index.php">🏠 Sistema</a>
        <a href="flutter_list_v3.php" target="_blank">📊 JSON List</a>
        <a href="qrcodes/" target="_blank">🗂️ QR Codes</a>
        <a href="#swagger-section">📋 API Docs</a>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    
    <script>
        // Inicializar Materialize
        M.AutoInit();
        
        // Scroll suave para seções
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Toast de boas-vindas
        setTimeout(() => {
            M.toast({
                html: '<i class="material-icons left">info</i>Explore a documentação da API Track Plant!',
                classes: 'blue rounded',
                displayLength: 4000
            });
        }, 1000);
    </script>
</body>
</html>