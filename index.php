<?php
// PHP code can go here if needed
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Track Plant - Sistema de QR Code para Plantas</title>
    
    <!-- Material Design -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link href="assets/css/styles.css" rel="stylesheet">
    
    <meta name="description" content="API Track Plant - Sistema avançado de geração e gerenciamento de QR Codes para rastreamento de plantas">
    <meta name="keywords" content="qr code, plantas, rastreamento, api, track plant">
</head>
<body>
    <nav class="navbar-custom">
        <div class="nav-wrapper container">
            <span class="brand-logo">
                <i class="material-icons">eco</i>
                API Track Plant
            </span>
            <ul id="nav-mobile" class="right">
                <li><a href="docs.php"><i class="material-icons left">description</i>API Docs</a></li>
                <li><a href="qrcodes/" target="_blank"><i class="material-icons left">folder</i>QR Codes</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col s12">
                <div class="structure-info">
                    <h4><i class="material-icons small">info</i> Sistema de Rastreamento</h6>
                    <p><strong>Formato:</strong> PlantId-Data-Hash</p>
                    <p><strong>Exemplo:</strong> PLT001-20250825-abc12345</p>
                    <p><strong>Dados no QR:</strong> ID, Nome, Status, Criação, Tipo e metadados</p>
                    <p><strong>Compatibilidade:</strong> FlutterFlow, Web API e Mobile</p>
                    <p><strong>Formatos:</strong> PNG (Recomendado) e SVG</p>
                    <p><strong>📋 Documentação:</strong> <a href="docs.php" style="color: var(--primary-color); text-decoration: underline;">Acesse a documentação completa da API</a></p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col s12">
                <ul class="tabs">
                    <li class="tab col s4"><a href="#generate" class="active">Gerar QR</a></li>
                    <li class="tab col s4"><a href="#list">Gerenciar QRs</a></li>
                    <li class="tab col s4"><a href="#search">Buscar</a></li>
                </ul>
            </div>

            <!-- Tab Gerar QR -->
            <div id="generate" class="col s12 tab-content">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">
                            <i class="material-icons">add_circle</i>
                            Gerar Novo QR Code
                        </span>
                        
                        <form id="generateForm">
                            <!-- Campos Obrigatórios -->
                            <div class="row">
                                <div class="input-field col s12 m6">
                                    <input 
                                        id="plant_id" 
                                        name="plant_id" 
                                        type="text" 
                                        class="validate"
                                        placeholder="Ex: PLT001, PLANT_A, ESP001"
                                        required
                                    >
                                    <label for="plant_id">ID da Planta *</label>
                                    <span class="helper-text">
                                        Identificador único da planta
                                    </span>
                                </div>
                                <div class="input-field col s12 m6">
                                    <input 
                                        id="plant_name" 
                                        name="plant_name" 
                                        type="text" 
                                        placeholder="Ex: Rosa Vermelha, Tulipa Amarela"
                                    >
                                    <label for="plant_name">Nome da Planta</label>
                                    <span class="helper-text">
                                        Nome descritivo da planta
                                    </span>
                                </div>
                            </div>

                            <!-- Campos de Status e Tipo -->
                            <div class="row">
                                <div class="input-field col s12 m6">
                                    <select id="plant_status" name="plant_status">
                                        <option value="" selected>Selecione o status</option>
                                        <option value="Saudável">🟢 Saudável</option>
                                        <option value="Em Crescimento">🟡 Em Crescimento</option>
                                        <option value="Precisa Atenção">🟠 Precisa Atenção</option>
                                        <option value="Crítica">🔴 Crítica</option>
                                        <option value="Nova">🆕 Nova</option>
                                        <option value="Transplantada">🔄 Transplantada</option>
                                    </select>
                                    <label for="plant_status">Status da Planta</label>
                                </div>
                                <div class="input-field col s12 m6">
                                    <select id="plant_type" name="plant_type">
                                        <option value="" selected>Selecione o tipo</option>
                                        <option value="Ornamental">🌺 Ornamental</option>
                                        <option value="Medicinal">🌿 Medicinal</option>
                                        <option value="Hortaliça">🥬 Hortaliça</option>
                                        <option value="Árvore">🌳 Árvore</option>
                                        <option value="Suculenta">🌵 Suculenta</option>
                                        <option value="Trepadeira">🌱 Trepadeira</option>
                                        <option value="Erva">🌾 Erva</option>
                                    </select>
                                    <label for="plant_type">Tipo da Planta</label>
                                </div>
                            </div>

                            <!-- Campos Opcionais Expandidos -->
                            <div id="additional-fields" style="display: none;">
                                <div class="row">
                                    <div class="input-field col s12 m6">
                                        <input 
                                            id="plant_species" 
                                            name="plant_species" 
                                            type="text" 
                                            placeholder="Ex: Rosa gallica, Aloe vera"
                                        >
                                        <label for="plant_species">Espécie</label>
                                    </div>
                                    <div class="input-field col s12 m6">
                                        <input 
                                            id="plant_location" 
                                            name="plant_location" 
                                            type="text" 
                                            placeholder="Ex: Jardim A, Estufa 1, Vaso 12"
                                        >
                                        <label for="plant_location">Localização</label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="input-field col s12 m6">
                                        <input 
                                            id="plant_age" 
                                            name="plant_age" 
                                            type="text" 
                                            placeholder="Ex: 3 meses, 1 ano, 2 semanas"
                                        >
                                        <label for="plant_age">Idade/Tempo</label>
                                    </div>
                                    <div class="input-field col s12 m6">
                                        <input 
                                            id="plant_notes" 
                                            name="plant_notes" 
                                            type="text" 
                                            placeholder="Ex: Regar 2x por semana, Sol direto"
                                        >
                                        <label for="plant_notes">Observações</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Botão para mostrar/ocultar campos extras -->
                            <div class="row">
                                <div class="col s12">
                                    <button 
                                        type="button" 
                                        id="toggle-fields" 
                                        class="btn waves-effect waves-light blue-grey"
                                        onclick="toggleAdditionalFields()"
                                    >
                                        <i class="material-icons left">add</i>
                                        <span id="toggle-text">Adicionar Mais Campos</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Formato -->
                            <div class="row">
                                <div class="input-field col s12 m6">
                                    <select id="format" name="format">
                                        <option value="png" selected>PNG (Recomendado)</option>
                                        <option value="svg">SVG (Vetorial)</option>
                                    </select>
                                    <label for="format">Formato</label>
                                </div>
                                <div class="col s12 m6" style="padding-top: 20px;">
                                    <p>
                                        <label>
                                            <input type="checkbox" id="include_timestamp" checked />
                                            <span>Incluir timestamp nos dados do QR</span>
                                        </label>
                                    </p>
                                </div>
                            </div>

                            <!-- Botão Gerar -->
                            <div class="row">
                                <div class="input-field col s12">
                                    <button 
                                        class="btn waves-effect waves-light green full-width" 
                                        type="submit"
                                    >
                                        <i class="material-icons left">qr_code</i>
                                        Gerar QR Code com Dados Completos
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Loading -->
                        <div class="loading" style="display: none;">
                            <div class="preloader-wrapper small active">
                                <div class="spinner-layer spinner-green-only">
                                    <div class="circle-clipper left">
                                        <div class="circle"></div>
                                    </div>
                                    <div class="gap-patch">
                                        <div class="circle"></div>
                                    </div>
                                    <div class="circle-clipper right">
                                        <div class="circle"></div>
                                    </div>
                                </div>
                            </div>
                            <p>Gerando QR Code...</p>
                        </div>

                        <!-- Mensagens -->
                        <div class="success-msg" style="display: none;"></div>
                        <div class="error-msg" style="display: none;"></div>

                        <!-- Resultado do QR -->
                        <div id="qr-result" style="display: none;">
                            <div class="divider" style="background-color: var(--border-color); margin: 20px 0;"></div>
                            <h6>QR Code Gerado com Sucesso:</h6>
                            <div class="qr-preview" id="qr-preview"></div>
                            <div class="row">
                                <div class="col s12 m6">
                                    <p><strong>ID da Planta:</strong> <span id="result-plant-id"></span></p>
                                    <p><strong>Nome:</strong> <span id="result-plant-name"></span></p>
                                    <p><strong>QR ID:</strong> <span id="result-qr-id"></span></p>
                                    <p><strong>Status:</strong> <span id="result-status"></span></p>
                                </div>
                                <div class="col s12 m6">
                                    <p><strong>Tipo:</strong> <span id="result-type"></span></p>
                                    <p><strong>Estrutura:</strong> <span id="result-structure"></span></p>
                                    <p><strong>Criado em:</strong> <span id="result-created"></span></p>
                                    <p><strong>Dados no QR:</strong> <span id="result-data-size"></span></p>
                                </div>
                            </div>
                            
                            <!-- Preview dos dados que estão no QR Code -->
                            <div class="row">
                                <div class="col s12">
                                    <div class="card-panel" style="background: var(--hover-bg); border: 1px solid var(--border-color);">
                                        <h6><i class="material-icons tiny">code</i> Dados incluídos no QR Code:</h6>
                                        <div id="qr-data-preview" style="font-family: monospace; font-size: 0.9rem; color: var(--accent-color);"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col s12 m3">
                                    <a id="download-link" class="btn waves-effect waves-light blue full-width" target="_blank">
                                        <i class="material-icons left">download</i>
                                        Download
                                    </a>
                                </div>
                                <div class="col s12 m3">
                                    <button id="copy-url" class="btn waves-effect waves-light orange full-width">
                                        <i class="material-icons left">content_copy</i>
                                        Copiar URL
                                    </button>
                                </div>
                                <div class="col s12 m3">
                                    <button id="copy-data" class="btn waves-effect waves-light purple full-width">
                                        <i class="material-icons left">assignment</i>
                                        Copiar Dados
                                    </button>
                                </div>
                                <div class="col s12 m3">
                                    <button onclick="document.getElementById('qr-result').style.display='none'" 
                                            class="btn waves-effect waves-light grey full-width">
                                        <i class="material-icons left">close</i>
                                        Fechar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Listar QRs -->
            <div id="list" class="col s12 tab-content">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">
                            <i class="material-icons">list</i>
                            Gerenciar QR Codes
                        </span>
                        
                        <div class="row">
                            <div class="col s12 m6">
                                <button id="refresh-list" class="btn waves-effect waves-light green">
                                    <i class="material-icons left">refresh</i>
                                    Atualizar Lista
                                </button>
                            </div>
                            <div class="col s12 m6">
                                <button onclick="toggleSelectAll()" class="btn waves-effect waves-light grey">
                                    <i class="material-icons left">select_all</i>
                                    Selecionar Todos
                                </button>
                            </div>
                        </div>

                        <button id="delete-selected" class="btn waves-effect waves-light red" 
                                style="display: none;" onclick="deleteSelectedQRCodes()">
                            <i class="material-icons left">delete</i>
                            Deletar Selecionados
                        </button>

                        <div class="loading" id="list-loading" style="display: none;">
                            <div class="preloader-wrapper small active">
                                <div class="spinner-layer spinner-green-only">
                                    <div class="circle-clipper left">
                                        <div class="circle"></div>
                                    </div>
                                    <div class="gap-patch">
                                        <div class="circle"></div>
                                    </div>
                                    <div class="circle-clipper right">
                                        <div class="circle"></div>
                                    </div>
                                </div>
                            </div>
                            <p>Carregando lista...</p>
                        </div>

                        <div id="qr-list"></div>
                    </div>
                </div>
            </div>

            <!-- Tab Buscar -->
            <div id="search" class="col s12 tab-content">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">
                            <i class="material-icons">search</i>
                            Buscar QR Codes
                        </span>
                        
                        <div class="row">
                            <div class="input-field col s12">
                                <input id="search-input" type="text" placeholder="Digite o ID, nome da planta, status ou tipo...">
                                <label for="search-input">Buscar</label>
                                <span class="helper-text">
                                    Busca em tempo real - mínimo 2 caracteres (ID, nome, status, tipo, localização)
                                </span>
                            </div>
                        </div>

                        <div id="search-results"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="assets/js/app.js"></script>
    
    <script>
        // Função para mostrar/ocultar campos extras
        function toggleAdditionalFields() {
            const fields = document.getElementById('additional-fields');
            const toggleText = document.getElementById('toggle-text');
            const toggleIcon = document.querySelector('#toggle-fields i');
            
            if (fields.style.display === 'none' || fields.style.display === '') {
                fields.style.display = 'block';
                toggleText.textContent = 'Ocultar Campos Extras';
                toggleIcon.textContent = 'remove';
            } else {
                fields.style.display = 'none';
                toggleText.textContent = 'Adicionar Mais Campos';
                toggleIcon.textContent = 'add';
            }
        }
        
        // Inicializar materialize components
        document.addEventListener('DOMContentLoaded', function() {
            M.FormSelect.init(document.querySelectorAll('select'));
            M.updateTextFields();
        });
    </script>
</body>
</html>
