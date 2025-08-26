<?php
/**
 * Sistema de Geração de QR Code - Versão 3.0 Enhanced
 * Estrutura: IdPlant-data (ex: PLT001-20250825) + dados otimizados no QR
 * Compatible com Index e FlutterFlow
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Apenas requisições POST são permitidas',
        'qr_code_url' => null
    ]);
    exit;
}

// Verificar se o autoload existe
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Biblioteca QR Code não instalada. Execute: composer install',
        'qr_code_url' => null
    ]);
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

try {
    // Captura o plant_id de diferentes fontes
    $input = null;
    
    // 1. Tenta JSON body primeiro
    $jsonInput = json_decode(file_get_contents('php://input'), true);
    if ($jsonInput) {
        $input = $jsonInput;
    }
    
    // 2. Tenta POST form data se não encontrou JSON
    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }
    
    // Extrair dados básicos
    $plantId = trim($input['plant_id'] ?? '');
    $format = strtolower($input['format'] ?? 'png');
    
    // Validação
    if (!$plantId) {
        throw new Exception('Campo plant_id é obrigatório e não pode estar vazio');
    }
    
    // Validar formato
    if (!in_array($format, ['png', 'svg'])) {
        $format = 'png';
    }
    
    // Criar estrutura de diretórios
    $dirs = ['qrcodes', 'data'];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new Exception("Não foi possível criar o diretório: $dir");
            }
        }
    }
    
    // Gerar identificadores
    $currentDate = date('Ymd');
    $timestamp = time();
    $uniqueHash = substr(md5(uniqid($plantId, true)), 0, 8);
    
    // Estrutura: PLT001-20250825-hash8chars
    $qrId = $plantId . '-' . $currentDate . '-' . $uniqueHash;
    
    // ===== CONSTRUIR DADOS OTIMIZADOS PARA O QR CODE =====
    
    // Função para construir dados do QR de forma inteligente
    function buildOptimizedQRData($input, $qrId) {
        $qrData = [
            // Sempre incluir - dados essenciais
            'id' => $input['plant_id'],
            'qr_id' => $qrId,
            'created' => date('Y-m-d H:i:s')
        ];
        
        // Incluir se preenchido e não vazio
        if (!empty($input['plant_name'])) {
            $qrData['name'] = trim($input['plant_name']);
        }
        
        if (!empty($input['plant_status'])) {
            $qrData['status'] = trim($input['plant_status']);
        }
        
        if (!empty($input['plant_type'])) {
            $qrData['type'] = trim($input['plant_type']);
        }
        
        if (!empty($input['plant_species'])) {
            $qrData['species'] = trim($input['plant_species']);
        }
        
        if (!empty($input['plant_location'])) {
            $qrData['location'] = trim($input['plant_location']);
        }
        
        if (!empty($input['plant_age'])) {
            $qrData['age'] = trim($input['plant_age']);
        }
        
        // Observações só se não for muito longa (máximo 100 caracteres)
        if (!empty($input['plant_notes'])) {
            $notes = trim($input['plant_notes']);
            if (strlen($notes) <= 100) {
                $qrData['notes'] = $notes;
            }
        }
        
        // Timestamp opcional
        if (isset($input['include_timestamp']) && $input['include_timestamp']) {
            $qrData['timestamp'] = date('c'); // ISO 8601
        }
        
        // Adicionar versão do sistema
        $qrData['system'] = 'TrackPlant_v3';
        
        return $qrData;
    }
    
    // Construir dados otimizados
    $qrDataContent = buildOptimizedQRData($input, $qrId);
    $qrDataJson = json_encode($qrDataContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $qrDataSize = strlen($qrDataJson);
    
    // Verificar se os dados não estão muito grandes
    $optimizationApplied = false;
    if ($qrDataSize > 800) {
        // Se muito grande, usar apenas dados essenciais
        $qrDataContent = [
            'id' => $input['plant_id'],
            'qr_id' => $qrId,
            'name' => $input['plant_name'] ?? '',
            'status' => $input['plant_status'] ?? '',
            'created' => date('Y-m-d H:i:s'),
            'system' => 'TrackPlant_v3'
        ];
        $qrDataJson = json_encode($qrDataContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $qrDataSize = strlen($qrDataJson);
        $optimizationApplied = true;
    }
    
    // Configuração do QR Code
    $options = new QROptions([
        'imageBase64' => false,
        'scale' => 8,
        'imageTransparent' => false,
        'outputType' => $format === 'svg' ? QRCode::OUTPUT_MARKUP_SVG : QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel' => QRCode::ECC_M,
    ]);

    $qrCode = new QRCode($options);
    
    // Definir caminho do arquivo
    $filename = $qrId . '.' . $format;
    $filepath = 'qrcodes/' . $filename;
    
    try {
        // Gerar QR Code com os dados JSON
        $qrContent = $qrCode->render($qrDataJson);
        
        // Salvar arquivo
        if (file_put_contents($filepath, $qrContent) === false) {
            throw new Exception('Falha ao salvar arquivo ' . strtoupper($format));
        }
        
    } catch (Exception $e) {
        throw new Exception('Erro ao gerar QR Code: ' . $e->getMessage());
    }

    // Verificar se o arquivo foi criado
    if (!file_exists($filepath)) {
        throw new Exception('Arquivo QR Code não foi criado corretamente');
    }

    // URL do QR Code
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $baseUrl = rtrim($protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']), '/');
    $qrUrl = $baseUrl . '/' . $filepath;

    // Registro detalhado no sistema
    $registro = [
        'qr_id' => $qrId,
        'plant_id' => $plantId,
        'plant_name' => $input['plant_name'] ?? '',
        'plant_status' => $input['plant_status'] ?? '',
        'plant_type' => $input['plant_type'] ?? '',
        'plant_species' => $input['plant_species'] ?? '',
        'plant_location' => $input['plant_location'] ?? '',
        'plant_age' => $input['plant_age'] ?? '',
        'plant_notes' => $input['plant_notes'] ?? '',
        'filename' => $filename,
        'filepath' => $filepath,
        'qr_code_url' => $qrUrl,
        'format' => $format,
        'created_at' => date('Y-m-d H:i:s'),
        'structure' => $plantId . '-' . $currentDate,
        'hash' => $uniqueHash,
        'size_kb' => round(filesize($filepath) / 1024, 2),
        'qr_data_content' => $qrDataContent,
        'qr_data_size' => $qrDataSize,
        'include_timestamp' => $input['include_timestamp'] ?? false,
        'optimization_applied' => $optimizationApplied
    ];

    // Salvar no registro
    $registryFile = 'data/qr_registry.json';
    $registries = [];
    
    if (file_exists($registryFile)) {
        $registries = json_decode(file_get_contents($registryFile), true) ?: [];
    }
    
    $registries[] = $registro;
    
    if (file_put_contents($registryFile, json_encode($registries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
        // Log do erro, mas não falha a operação
        error_log("Falha ao salvar registro em: $registryFile");
    }

    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'QR Code gerado com sucesso',
        'qr_code_url' => $qrUrl,
        'plant_id' => $plantId,
        'plant_name' => $input['plant_name'] ?? '',
        'plant_status' => $input['plant_status'] ?? '',
        'plant_type' => $input['plant_type'] ?? '',
        'qr_id' => $qrId,
        'filename' => $filename,
        'format' => $format,
        'created_at' => $registro['created_at'],
        'structure' => $registro['structure'],
        'hash' => $uniqueHash,
        'qr_data_content' => $qrDataContent,
        'qr_data_size' => $qrDataSize,
        'qr_data_json' => $qrDataJson,
        'optimization_applied' => $optimizationApplied
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'qr_code_url' => null,
        'plant_id' => $plantId ?? null,
        'qr_id' => null,
        'created_at' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
?>
