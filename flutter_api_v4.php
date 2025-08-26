<?php
/**
 * Sistema de Geração de QR Code Multi-Tipos - Versão 4.0
 * Suporta: Plantas, Produtos, Equipamentos, Documentos, etc.
 * Estrutura: Tipo-IdItem-Data-Hash (ex: PLANT-PLT001-20250825-abc12345)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Apenas POST permitido', 'qr_code_url' => null]);
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

try {
    // Captura dados de entrada
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    // Configuração dos tipos suportados
    $supportedTypes = [
        'PLANT' => [
            'name' => 'Plantas',
            'icon' => 'eco',
            'folder' => 'plants',
            'color' => '#4CAF50',
            'fields' => [
                'name' => 'Nome da Planta',
                'species' => 'Espécie',
                'location' => 'Localização',
                'age' => 'Idade',
                'status' => 'Status',
                'care_level' => 'Nível de Cuidado',
                'last_watered' => 'Última Rega',
                'notes' => 'Observações'
            ]
        ],
        'PRODUCT' => [
            'name' => 'Produtos',
            'icon' => 'shopping_cart',
            'folder' => 'products',
            'color' => '#2196F3',
            'fields' => [
                'name' => 'Nome do Produto',
                'category' => 'Categoria',
                'brand' => 'Marca',
                'price' => 'Preço',
                'status' => 'Status',
                'sku' => 'SKU',
                'supplier' => 'Fornecedor',
                'description' => 'Descrição'
            ]
        ],
        'EQUIPMENT' => [
            'name' => 'Equipamentos',
            'icon' => 'build',
            'folder' => 'equipment',
            'color' => '#FF9800',
            'fields' => [
                'name' => 'Nome do Equipamento',
                'model' => 'Modelo',
                'serial' => 'Número de Série',
                'location' => 'Localização',
                'status' => 'Status',
                'maintenance_date' => 'Última Manutenção',
                'warranty' => 'Garantia',
                'responsible' => 'Responsável'
            ]
        ],
        'DOCUMENT' => [
            'name' => 'Documentos',
            'icon' => 'description',
            'folder' => 'documents',
            'color' => '#9C27B0',
            'fields' => [
                'name' => 'Nome do Documento',
                'type' => 'Tipo',
                'department' => 'Departamento',
                'version' => 'Versão',
                'status' => 'Status',
                'author' => 'Autor',
                'expiry_date' => 'Data de Expiração',
                'classification' => 'Classificação'
            ]
        ],
        'ASSET' => [
            'name' => 'Ativos',
            'icon' => 'account_balance',
            'folder' => 'assets',
            'color' => '#607D8B',
            'fields' => [
                'name' => 'Nome do Ativo',
                'value' => 'Valor',
                'condition' => 'Condição',
                'owner' => 'Proprietário',
                'status' => 'Status',
                'purchase_date' => 'Data de Compra',
                'depreciation' => 'Depreciação',
                'insurance' => 'Seguro'
            ]
        ]
    ];
    
    // Validação dos campos obrigatórios
    $type = strtoupper($input['type'] ?? 'PLANT'); // Default: PLANT para compatibilidade
    $itemId = trim($input['item_id'] ?? $input['plant_id'] ?? ''); // Compatibilidade com plant_id
    $format = $input['format'] ?? 'png';
    
    if (!$itemId) {
        throw new Exception('Campo item_id é obrigatório');
    }
    
    if (!isset($supportedTypes[$type])) {
        throw new Exception('Tipo não suportado. Tipos válidos: ' . implode(', ', array_keys($supportedTypes)));
    }
    
    $typeConfig = $supportedTypes[$type];
    
    // Criar estrutura de diretórios
    $baseDir = 'qrcodes/' . $typeConfig['folder'];
    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0755, true);
    }
    
    if (!is_dir('data')) {
        mkdir('data', 0755, true);
    }
    
    // Gerar identificadores
    $currentDate = date('Ymd');
    $timestamp = time();
    $uniqueHash = substr(md5(uniqid($type . $itemId, true)), 0, 8);
    
    // Estrutura: TIPO-ItemId-Data-Hash
    $qrId = $type . '-' . $itemId . '-' . $currentDate . '-' . $uniqueHash;
    $filename = $qrId . '.' . $format;
    $filepath = $baseDir . '/' . $filename;
    
    // Coletar campos personalizados
    $customFields = [];
    foreach ($typeConfig['fields'] as $field => $label) {
        if (isset($input[$field]) && !empty(trim($input[$field]))) {
            $customFields[$field] = trim($input[$field]);
        }
    }
    
    // Dados para o QR Code
    $qrData = [
        'type' => $type,
        'type_name' => $typeConfig['name'],
        'item_id' => $itemId,
        'qr_id' => $qrId,
        'created_at' => date('Y-m-d H:i:s'),
        'fields' => $customFields,
        'url' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . 
                $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $filepath
    ];
    
    // Configuração do QR Code
    $options = new QROptions([
        'imageBase64' => false,
        'outputType' => $format === 'svg' ? 'svg' : 'png',
    ]);
    
    $qrCode = new QRCode($options);
    $qrContent = $qrCode->render(json_encode($qrData));
    
    // Salvar arquivo
    if (file_put_contents($filepath, $qrContent) === false) {
        throw new Exception('Falha ao salvar arquivo QR Code');
    }
    
    // URL do QR Code
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $baseUrl = rtrim($protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']), '/');
    $qrUrl = $baseUrl . '/' . $filepath;
    
    // Registro no sistema
    $registro = [
        'qr_id' => $qrId,
        'type' => $type,
        'type_name' => $typeConfig['name'],
        'type_icon' => $typeConfig['icon'],
        'type_color' => $typeConfig['color'],
        'item_id' => $itemId,
        'filename' => basename($filepath),
        'filepath' => $filepath,
        'qr_code_url' => $qrUrl,
        'format' => $format,
        'created_at' => date('Y-m-d H:i:s'),
        'structure' => $type . '-' . $itemId . '-' . $currentDate,
        'hash' => $uniqueHash,
        'size_kb' => round(filesize($filepath) / 1024, 2),
        'custom_fields' => $customFields
    ];
    
    // Salvar no registro geral
    $registryFile = 'data/qr_registry_v4.json';
    $registries = [];
    
    if (file_exists($registryFile)) {
        $registries = json_decode(file_get_contents($registryFile), true) ?: [];
    }
    
    $registries[] = $registro;
    file_put_contents($registryFile, json_encode($registries, JSON_PRETTY_PRINT));
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => "QR Code {$typeConfig['name']} gerado com sucesso",
        'qr_code_url' => $qrUrl,
        'type' => $type,
        'type_name' => $typeConfig['name'],
        'type_icon' => $typeConfig['icon'],
        'type_color' => $typeConfig['color'],
        'item_id' => $itemId,
        'qr_id' => $qrId,
        'filename' => basename($filepath),
        'format' => $format,
        'created_at' => $registro['created_at'],
        'structure' => $registro['structure'],
        'hash' => $uniqueHash,
        'custom_fields' => $customFields,
        'fields_count' => count($customFields)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'qr_code_url' => null,
        'type' => $type ?? null,
        'item_id' => $itemId ?? null,
        'created_at' => date('Y-m-d H:i:s')
    ]);
}
?>