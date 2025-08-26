// API Track Plant - JavaScript App Enhanced v3.0
document.addEventListener('DOMContentLoaded', function () {
    initializeMaterialize();
    loadQRList();
    setupEventListeners();
    setupFormValidation();
});

function initializeMaterialize() {
    M.Tabs.init(document.querySelectorAll('.tabs'));
    M.FormSelect.init(document.querySelectorAll('select'));
    M.updateTextFields();
}

function setupEventListeners() {
    document.getElementById('generateForm').addEventListener('submit', handleGenerateForm);
    document.getElementById('search-input').addEventListener('input', handleSearch);
    document.getElementById('refresh-list').addEventListener('click', loadQRList);

    // Preview em tempo real do tamanho dos dados
    setupRealTimePreview();
}

function setupRealTimePreview() {
    const inputs = ['plant_id', 'plant_name', 'plant_status', 'plant_type',
        'plant_species', 'plant_location', 'plant_age', 'plant_notes'];

    inputs.forEach(inputId => {
        const element = document.getElementById(inputId);
        if (element) {
            element.addEventListener('input', updateDataSizePreview);
            if (element.tagName === 'SELECT') {
                element.addEventListener('change', updateDataSizePreview);
            }
        }
    });

    document.getElementById('include_timestamp').addEventListener('change', updateDataSizePreview);
}

function updateDataSizePreview() {
    const plantData = collectFormData();
    const qrData = buildQRDataPreview(plantData);
    const dataSize = JSON.stringify(qrData).length;

    // Criar preview visual se não existir
    let previewElement = document.getElementById('data-size-preview');
    if (!previewElement) {
        previewElement = document.createElement('div');
        previewElement.id = 'data-size-preview';
        previewElement.style.cssText = `
            background: var(--hover-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            font-size: 0.9rem;
        `;

        const form = document.getElementById('generateForm');
        form.insertBefore(previewElement, form.querySelector('.row:last-child'));
    }

    // Determinar cor baseada no tamanho
    let sizeColor = 'green';
    let sizeStatus = '✅ Ideal';

    if (dataSize > 300 && dataSize <= 800) {
        sizeColor = 'orange';
        sizeStatus = '⚠️ Médio';
    } else if (dataSize > 800) {
        sizeColor = 'red';
        sizeStatus = '🔴 Grande (será otimizado)';
    }

    previewElement.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <strong>📊 Preview dos Dados do QR Code:</strong>
                <br>
                <span style="color: ${sizeColor}">
                    <strong>${dataSize} caracteres</strong> - ${sizeStatus}
                </span>
            </div>
            <button type="button" onclick="toggleDataPreview()" 
                    class="btn-small waves-effect waves-light blue-grey">
                <i class="material-icons">visibility</i>
            </button>
        </div>
        <div id="data-preview-content" style="display: none; margin-top: 10px;">
            <pre style="background: var(--primary-color); padding: 10px; border-radius: 4px; font-size: 0.8rem; color: var(--accent-color); overflow-x: auto;">${JSON.stringify(qrData, null, 2)}</pre>
        </div>
    `;
}

function toggleDataPreview() {
    const content = document.getElementById('data-preview-content');
    const button = event.target.closest('button');
    const icon = button.querySelector('i');

    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.textContent = 'visibility_off';
    } else {
        content.style.display = 'none';
        icon.textContent = 'visibility';
    }
}

function collectFormData() {
    return {
        plant_id: document.getElementById('plant_id').value.trim(),
        plant_name: document.getElementById('plant_name').value.trim(),
        plant_status: document.getElementById('plant_status').value,
        plant_type: document.getElementById('plant_type').value,
        plant_species: document.getElementById('plant_species').value.trim(),
        plant_location: document.getElementById('plant_location').value.trim(),
        plant_age: document.getElementById('plant_age').value.trim(),
        plant_notes: document.getElementById('plant_notes').value.trim(),
        format: document.getElementById('format').value,
        include_timestamp: document.getElementById('include_timestamp').checked
    };
}

function buildQRDataPreview(formData) {
    const qrData = {
        id: formData.plant_id,
        qr_id: `${formData.plant_id}-${getCurrentDate()}-xxxxxxxx`,
        created: new Date().toISOString().slice(0, 19).replace('T', ' ')
    };

    // Incluir apenas campos preenchidos
    if (formData.plant_name) qrData.name = formData.plant_name;
    if (formData.plant_status) qrData.status = formData.plant_status;
    if (formData.plant_type) qrData.type = formData.plant_type;
    if (formData.plant_species) qrData.species = formData.plant_species;
    if (formData.plant_location) qrData.location = formData.plant_location;
    if (formData.plant_age) qrData.age = formData.plant_age;

    // Observações só se não for muito longa
    if (formData.plant_notes && formData.plant_notes.length <= 100) {
        qrData.notes = formData.plant_notes;
    }

    if (formData.include_timestamp) {
        qrData.timestamp = new Date().toISOString();
    }

    qrData.system = 'TrackPlant_v3';

    return qrData;
}

function getCurrentDate() {
    const now = new Date();
    return now.getFullYear().toString() +
        (now.getMonth() + 1).toString().padStart(2, '0') +
        now.getDate().toString().padStart(2, '0');
}

function setupFormValidation() {
    const plantIdInput = document.getElementById('plant_id');
    const notesInput = document.getElementById('plant_notes');

    // Validação do ID da planta
    plantIdInput.addEventListener('input', function () {
        const value = this.value.trim();
        const isValid = /^[A-Za-z0-9_-]+$/.test(value);

        if (value && !isValid) {
            this.classList.add('invalid');
            showValidationMessage('plant_id', '❌ Use apenas letras, números, hífen e underscore');
        } else {
            this.classList.remove('invalid');
            hideValidationMessage('plant_id');
        }
    });

    // Validação do tamanho das observações
    notesInput.addEventListener('input', function () {
        const length = this.value.length;
        const helperText = this.parentElement.querySelector('.helper-text');

        if (length > 100) {
            this.classList.add('invalid');
            helperText.textContent = `⚠️ Observações muito longas (${length}/100). Será omitida do QR Code.`;
            helperText.style.color = 'var(--error-color)';
        } else if (length > 80) {
            this.classList.remove('invalid');
            helperText.textContent = `⚠️ Observações próximas do limite (${length}/100)`;
            helperText.style.color = 'orange';
        } else {
            this.classList.remove('invalid');
            helperText.textContent = 'Ex: Regar 2x por semana, Sol direto';
            helperText.style.color = '';
        }
    });
}

function showValidationMessage(inputId, message) {
    let msgElement = document.getElementById(`${inputId}-validation`);
    if (!msgElement) {
        msgElement = document.createElement('div');
        msgElement.id = `${inputId}-validation`;
        msgElement.style.cssText = 'color: var(--error-color); font-size: 0.8rem; margin-top: 5px;';
        document.getElementById(inputId).parentElement.appendChild(msgElement);
    }
    msgElement.textContent = message;
}

function hideValidationMessage(inputId) {
    const msgElement = document.getElementById(`${inputId}-validation`);
    if (msgElement) {
        msgElement.remove();
    }
}

// Função principal para gerar QR Code
async function handleGenerateForm(e) {
    e.preventDefault();

    const plantData = collectFormData();

    if (!plantData.plant_id) {
        showError('ID da planta é obrigatório');
        return;
    }

    // Validar ID da planta
    if (!/^[A-Za-z0-9_-]+$/.test(plantData.plant_id)) {
        showError('ID da planta deve conter apenas letras, números, hífen e underscore');
        return;
    }

    showLoading(true);
    hideMessages();

    try {
        const response = await fetch('flutter_api_v3.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(plantData)
        });

        const data = await response.json();

        if (data.success) {
            showQRResult(data, plantData);
            showSuccess(`🎉 QR Code gerado com sucesso! ${data.optimization_applied ? '(Dados otimizados)' : ''}`);

            // Limpar apenas o ID obrigatório
            document.getElementById('plant_id').value = '';
            M.updateTextFields();

            setTimeout(loadQRList, 1000);
        } else {
            showError(data.message || 'Erro ao gerar QR Code');
        }
    } catch (error) {
        showError('Erro de conexão: ' + error.message);
    } finally {
        showLoading(false);
    }
}

// Função melhorada para exibir resultado do QR
function showQRResult(data, originalData) {
    document.getElementById('qr-result').style.display = 'block';
    document.getElementById('qr-preview').innerHTML =
        `<img src="${data.qr_code_url}" alt="QR Code" style="width: 100%; max-width: 300px;" />`;

    // Dados básicos
    document.getElementById('result-plant-id').textContent = data.plant_id;
    document.getElementById('result-plant-name').textContent = data.plant_name || 'Não informado';
    document.getElementById('result-qr-id').textContent = data.qr_id;
    document.getElementById('result-status').textContent = data.plant_status || 'Não informado';
    document.getElementById('result-type').textContent = data.plant_type || 'Não informado';
    document.getElementById('result-structure').textContent = data.structure;
    document.getElementById('result-created').textContent = data.created_at;
    document.getElementById('result-data-size').innerHTML =
        `<strong>${data.qr_data_size} caracteres</strong> ${data.optimization_applied ? '<span style="color: orange;">(otimizado)</span>' : '<span style="color: green;">(ideal)</span>'}`;

    // Preview dos dados do QR Code
    document.getElementById('qr-data-preview').innerHTML =
        '<pre>' + JSON.stringify(data.qr_data_content, null, 2) + '</pre>';

    // Configurar botões
    document.getElementById('download-link').href = data.qr_code_url;
    document.getElementById('copy-url').onclick = () => copyToClipboard(data.qr_code_url);
    document.getElementById('copy-data').onclick = () => copyToClipboard(data.qr_data_json);

    // Scroll suave para o resultado
    document.getElementById('qr-result').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });

    // Toast informativo sobre otimização
    if (data.optimization_applied) {
        setTimeout(() => {
            M.toast({
                html: '<i class="material-icons left">info</i>Dados otimizados automaticamente para QR Code menor',
                classes: 'orange rounded',
                displayLength: 5000
            });
        }, 1000);
    }
}

// Resto das funções mantém o padrão anterior...
async function loadQRList() {
    const listLoading = document.getElementById('list-loading');
    listLoading.style.display = 'block';

    try {
        const response = await fetch('flutter_list_v3.php');
        const data = await response.json();

        if (data.success) {
            displayQRList(data.qr_codes);
        } else {
            document.getElementById('qr-list').innerHTML =
                '<p class="red-text">Erro ao carregar lista: ' + data.message + '</p>';
        }
    } catch (error) {
        document.getElementById('qr-list').innerHTML =
            '<p class="red-text">Erro de conexão: ' + error.message + '</p>';
    } finally {
        listLoading.style.display = 'none';
    }
}

function displayQRList(qrCodes) {
    const listContainer = document.getElementById('qr-list');

    if (qrCodes.length === 0) {
        listContainer.innerHTML = `
            <div class="center-align" style="padding: 40px;">
                <i class="material-icons large" style="color: var(--text-secondary);">qr_code</i>
                <p class="grey-text">Nenhum QR Code encontrado.</p>
                <p class="grey-text">Gere seu primeiro QR Code na aba "Gerar QR"</p>
            </div>
        `;
        return;
    }

    let html = `
        <div class="row">
            <div class="col s12">
                <div class="chip" style="background: var(--accent-color); color: var(--primary-color);">
                    <i class="material-icons">analytics</i>
                    Total: ${qrCodes.length} QR Codes
                </div>
            </div>
        </div>
    `;

    qrCodes.forEach((qr, index) => {
        const createdDate = new Date(qr.created_at).toLocaleString('pt-BR');
        const isRecent = (new Date() - new Date(qr.created_at)) < 24 * 60 * 60 * 1000;

        // Usar dados do novo formato se disponível
        const displayName = qr.plant_name || 'Nome não informado';
        const displayStatus = qr.plant_status || 'Status não informado';
        const displayType = qr.plant_type || 'Tipo não informado';
        const dataSize = qr.qr_data_size ? `${qr.qr_data_size} chars` : 'N/A';

        html += `
            <div class="qr-item" data-index="${index}">
                <div class="row valign-wrapper">
                    <div class="col s1">
                        <label>
                            <input type="checkbox" class="qr-checkbox" data-qr-id="${qr.qr_id}" 
                                   onchange="updateDeleteButton()" />
                            <span></span>
                        </label>
                    </div>
                    <div class="col s12 m8 l8">
                        <div class="row">
                            <div class="col s12 m6">
                                <p><strong>🌱 Planta:</strong> ${qr.plant_id} ${isRecent ? '<span class="new badge green" data-badge-caption="novo"></span>' : ''}</p>
                                <p><strong>📛 Nome:</strong> ${displayName}</p>
                                <p><strong>🔗 QR ID:</strong> <code style="background: var(--hover-bg); padding: 2px 6px; border-radius: 4px;">${qr.qr_id}</code></p>
                            </div>
                            <div class="col s12 m6">
                                <p><strong>📊 Status:</strong> ${displayStatus}</p>
                                <p><strong>🏷️ Tipo:</strong> ${displayType}</p>
                                <p><strong>📅 Criado:</strong> ${createdDate}</p>
                                <p><strong>💾 Arquivo:</strong> ${qr.size_kb} KB | <strong>📋 Dados:</strong> ${dataSize}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col s12 m3 l3">
                        <div class="row">
                            <div class="col s12">
                                <a href="${qr.qr_code_url}" target="_blank" 
                                   class="btn waves-effect waves-light blue full-width">
                                    <i class="material-icons left">visibility</i>
                                    Ver QR
                                </a>
                            </div>
                            <div class="col s12">
                                <button onclick="copyToClipboard('${qr.qr_code_url}')"
                                        class="btn waves-effect waves-light orange full-width">
                                    <i class="material-icons left">content_copy</i>
                                    Copiar URL
                                </button>
                            </div>
                            <div class="col s12">
                                <button onclick="showQRDataModal('${qr.qr_id}')"
                                        class="btn waves-effect waves-light purple full-width">
                                    <i class="material-icons left">code</i>
                                    Ver Dados
                                </button>
                            </div>
                            <div class="col s12">
                                <button onclick="deleteQRCode('${qr.qr_id}', '${qr.plant_id}')"
                                        class="btn waves-effect waves-light red full-width">
                                    <i class="material-icons left">delete</i>
                                    Deletar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    listContainer.innerHTML = html;
    M.AutoInit();
}

function showQRDataModal(qrId) {
    // Encontrar dados do QR Code na lista carregada
    fetch('flutter_list_v3.php')
        .then(response => response.json())
        .then(data => {
            const qr = data.qr_codes.find(item => item.qr_id === qrId);
            if (qr && qr.qr_data_content) {
                const modalContent = `
                    <div class="modal-content">
                        <h4><i class="material-icons left">code</i>Dados no QR Code</h4>
                        <p><strong>QR ID:</strong> ${qr.qr_id}</p>
                        <p><strong>Tamanho:</strong> ${qr.qr_data_size || 'N/A'} caracteres</p>
                        <div style="background: var(--hover-bg); padding: 15px; border-radius: 8px; margin: 15px 0;">
                            <pre style="margin: 0; color: var(--accent-color); font-size: 0.9rem;">${JSON.stringify(qr.qr_data_content, null, 2)}</pre>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn waves-effect waves-light orange" onclick="copyToClipboard('${JSON.stringify(qr.qr_data_content)}')">
                            <i class="material-icons left">content_copy</i>Copiar JSON
                        </button>
                        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Fechar</a>
                    </div>
                `;

                // Criar e abrir modal
                const modalEl = document.createElement('div');
                modalEl.className = 'modal';
                modalEl.innerHTML = modalContent;
                document.body.appendChild(modalEl);

                const modal = M.Modal.init(modalEl);
                modal.open();

                // Remover modal quando fechado
                modalEl.addEventListener('click', function (e) {
                    if (e.target.classList.contains('modal-close')) {
                        setTimeout(() => {
                            document.body.removeChild(modalEl);
                        }, 300);
                    }
                });
            }
        })
        .catch(error => {
            M.toast({
                html: '<i class="material-icons left">error</i>Erro ao carregar dados do QR',
                classes: 'red rounded'
            });
        });
}

// Utility functions
function showLoading(show) {
    document.querySelector('.loading').style.display = show ? 'block' : 'none';
}

function showSuccess(message) {
    const el = document.querySelector('.success-msg');
    el.innerHTML = `<i class="material-icons left">check_circle</i>${message}`;
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 5000);
}

function showError(message) {
    const el = document.querySelector('.error-msg');
    el.innerHTML = `<i class="material-icons left">error</i>${message}`;
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 5000);
}

function hideMessages() {
    document.querySelector('.success-msg').style.display = 'none';
    document.querySelector('.error-msg').style.display = 'none';
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        M.toast({
            html: '<i class="material-icons left">content_copy</i>Copiado com sucesso!',
            classes: 'green rounded'
        });
    }).catch(() => {
        M.toast({
            html: '<i class="material-icons left">error</i>Erro ao copiar',
            classes: 'red rounded'
        });
    });
}

// Resto das funções (delete, search, etc.) mantém o mesmo padrão...
async function deleteQRCode(qrId, plantId) {
    if (!confirm(`🗑️ Confirmar Deleção\n\n🌱 Planta: ${plantId}\n🔗 QR ID: ${qrId}\n\n⚠️ Esta ação não pode ser desfeita.`)) {
        return;
    }

    try {
        const response = await fetch('flutter_delete_v3.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ qr_id: qrId })
        });

        const data = await response.json();

        if (data.success) {
            M.toast({
                html: `<i class="material-icons left">delete</i>QR Code ${plantId} deletado!`,
                classes: 'green rounded'
            });

            loadQRList();
            document.getElementById('search-results').innerHTML = '';
            document.getElementById('search-input').value = '';
        } else {
            M.toast({
                html: `<i class="material-icons left">error</i>Erro: ${data.message}`,
                classes: 'red rounded'
            });
        }
    } catch (error) {
        M.toast({
            html: `<i class="material-icons left">error</i>Erro: ${error.message}`,
            classes: 'red rounded'
        });
    }
}

function handleSearch() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    performSearch(searchTerm);
}

async function performSearch(searchTerm) {
    if (searchTerm.length < 2) {
        document.getElementById('search-results').innerHTML = `
            <div class="center-align" style="padding: 20px;">
                <i class="material-icons large" style="color: var(--text-secondary);">search</i>
                <p class="grey-text">Digite pelo menos 2 caracteres para buscar</p>
            </div>
        `;
        return;
    }

    try {
        const response = await fetch('flutter_list_v3.php');
        const data = await response.json();

        if (data.success) {
            const filtered = data.qr_codes.filter(qr => {
                return qr.plant_id.toLowerCase().includes(searchTerm) ||
                    qr.qr_id.toLowerCase().includes(searchTerm) ||
                    (qr.plant_name && qr.plant_name.toLowerCase().includes(searchTerm)) ||
                    (qr.plant_status && qr.plant_status.toLowerCase().includes(searchTerm)) ||
                    (qr.plant_type && qr.plant_type.toLowerCase().includes(searchTerm)) ||
                    (qr.plant_location && qr.plant_location.toLowerCase().includes(searchTerm));
            });

            displaySearchResults(filtered, searchTerm);
        }
    } catch (error) {
        document.getElementById('search-results').innerHTML =
            '<p class="red-text">Erro na busca: ' + error.message + '</p>';
    }
}

function displaySearchResults(results, searchTerm) {
    const container = document.getElementById('search-results');

    if (results.length === 0) {
        container.innerHTML = `
            <div class="center-align" style="padding: 20px;">
                <i class="material-icons large" style="color: var(--text-secondary);">search_off</i>
                <p class="grey-text">Nenhum resultado encontrado para "${searchTerm}"</p>
            </div>
        `;
        return;
    }

    let html = `
        <div class="row">
            <div class="col s12">
                <div class="chip" style="background: var(--accent-color); color: var(--primary-color);">
                    <i class="material-icons">search</i>
                    ${results.length} resultado(s) para "${searchTerm}"
                </div>
            </div>
        </div>
    `;

    results.forEach(qr => {
        const createdDate = new Date(qr.created_at).toLocaleString('pt-BR');
        const displayName = qr.plant_name || 'Nome não informado';
        const displayStatus = qr.plant_status || 'Status não informado';
        const displayType = qr.plant_type || 'Tipo não informado';

        html += `
            <div class="qr-item">
                <div class="row valign-wrapper">
                    <div class="col s12 m8">
                        <p><strong>🌱 Planta:</strong> ${highlightSearchTerm(qr.plant_id, searchTerm)}</p>
                        <p><strong>📛 Nome:</strong> ${highlightSearchTerm(displayName, searchTerm)}</p>
                        <p><strong>🔗 QR ID:</strong> <code>${highlightSearchTerm(qr.qr_id, searchTerm)}</code></p>
                        <p><strong>📊 Status:</strong> ${highlightSearchTerm(displayStatus, searchTerm)}</p>
                        <p><strong>🏷️ Tipo:</strong> ${highlightSearchTerm(displayType, searchTerm)}</p>
                        <p><strong>📅 Criado:</strong> ${createdDate}</p>
                    </div>
                    <div class="col s12 m4">
                        <a href="${qr.qr_code_url}" target="_blank" 
                           class="btn waves-effect waves-light blue full-width">
                            <i class="material-icons left">visibility</i>Ver QR
                        </a>
                        <button onclick="copyToClipboard('${qr.qr_code_url}')"
                                class="btn waves-effect waves-light orange full-width">
                            <i class="material-icons left">content_copy</i>Copiar
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function highlightSearchTerm(text, searchTerm) {
    if (!searchTerm || !text) return text;
    const regex = new RegExp(`(${searchTerm})`, 'gi');
    return text.replace(regex, '<mark style="background: var(--accent-color); color: var(--primary-color); padding: 2px 4px; border-radius: 3px;">$1</mark>');
}

function updateDeleteButton() {
    const checkboxes = document.querySelectorAll('.qr-checkbox:checked');
    const deleteButton = document.getElementById('delete-selected');

    if (checkboxes.length > 0) {
        deleteButton.style.display = 'inline-block';
        deleteButton.innerHTML = `
            <i class="material-icons left">delete</i>
            Deletar Selecionados (${checkboxes.length})
        `;
    } else {
        deleteButton.style.display = 'none';
    }
}

function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.qr-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);

    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });

    updateDeleteButton();

    M.toast({
        html: allChecked ?
            '<i class="material-icons left">clear</i>Seleção removida' :
            '<i class="material-icons left">done_all</i>Todos selecionados',
        classes: 'blue rounded'
    });
}

async function deleteSelectedQRCodes() {
    const checkboxes = document.querySelectorAll('.qr-checkbox:checked');
    const qrIds = Array.from(checkboxes).map(cb => cb.dataset.qrId);

    if (qrIds.length === 0) {
        M.toast({
            html: '<i class="material-icons left">warning</i>Nenhum QR Code selecionado',
            classes: 'orange rounded'
        });
        return;
    }

    if (!confirm(`🗑️ Confirmar Deleção em Lote\n\n📊 Quantidade: ${qrIds.length} QR Codes\n\n⚠️ Esta ação não pode ser desfeita.`)) {
        return;
    }

    let deleted = 0;
    let errors = 0;

    M.toast({
        html: '<i class="material-icons left">hourglass_empty</i>Deletando QR Codes...',
        classes: 'blue rounded'
    });

    for (const qrId of qrIds) {
        try {
            const response = await fetch('flutter_delete_v3.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ qr_id: qrId })
            });

            const data = await response.json();

            if (data.success) {
                deleted++;
            } else {
                errors++;
            }
        } catch (error) {
            errors++;
        }
    }

    if (deleted > 0) {
        M.toast({
            html: `<i class="material-icons left">done</i>${deleted} QR Codes deletados!`,
            classes: 'green rounded'
        });
    }

    if (errors > 0) {
        M.toast({
            html: `<i class="material-icons left">warning</i>${errors} QR Codes falharam`,
            classes: 'orange rounded'
        });
    }

    loadQRList();
}

// Função para mostrar/ocultar campos extras (compatibilidade com index.php)
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

    // Atualizar preview quando mostrar/ocultar campos
    updateDataSizePreview();
}
