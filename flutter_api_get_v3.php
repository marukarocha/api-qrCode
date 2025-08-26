<?php
/**
 * API GET Simplificada para FlutterFlow
 * URL: https://qrcode.seedapp.dev/flutter_api_get_v3.php?plant_id=PLT001
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'message' => 'Apenas requisições GET são permitidas',
        'qr_code_url' => ''
    ]);
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

try {
    // Pega plant_id da URL
    $plantId = $_GET['plant_id'] ?? '';
    
    if (empty(trim($plantId))) {
        throw new Exception('Parâmetro plant_id é obrigatório');
    }
    
    $plantId = trim($plantId);
    
    // Cria diretórios se não existirem
    $dirs = ['qrcodes', 'data'];
    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    // Gera identificadores
    $currentDate = date('Ymd');
    $timestamp = time();
    $uniqueHash = substr(md5(uniqid($plantId, true)), 0, 8);
    
    $qrId = $plantId . '-' . $currentDate . '-' . $uniqueHash;
    $filename = $qrId . '.svg';
    $filepath = 'qrcodes/' . $filename;
    
    // Gera QR Code
    $options = new QROptions([
        'outputType' => QRCode::OUTPUT_MARKUP_SVG,
        'svgViewBoxSize' => 530,
        'scale' => 10,
    ]);
    
    $qrCode = new QRCode($options);
    $svgRaw = $qrCode->render($plantId);
    
    // Remove prefixo base64 se existir
    if (strpos($svgRaw, 'data:image/svg+xml;base64,') === 0) {
        $base64Data = substr($svgRaw, strlen('data:image/svg+xml;base64,'));
        $svg = base64_decode($base64Data);
    } else {
        $svg = $svgRaw;
    }
    
    // Salva arquivo
    if (file_put_contents($filepath, $svg) === false) {
        throw new Exception('Falha ao salvar arquivo SVG');
    }
    
    // URL do QR Code
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $baseUrl = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
    $qrUrl = $baseUrl . '/' . $filepath;
    
    // Salva no registro
    $registro = [
        'qr_id' => $qrId,
        'plant_id' => $plantId,
        'filename' => $filename,
        'filepath' => $filepath,
        'qr_code_url' => $qrUrl,
        'created_at' => date('Y-m-d H:i:s'),
        'timestamp' => $timestamp,
        'date_part' => $currentDate,
        'size_bytes' => strlen($svg),
        'hash' => $uniqueHash
    ];
    
    $registryFile = 'data/qr_registry_v3.json';
    $registries = [];
    
    if (file_exists($registryFile)) {
        $content = file_get_contents($registryFile);
        $registries = json_decode($content, true) ?: [];
    }
    
    $registries[] = $registro;
    file_put_contents($registryFile, json_encode($registries, JSON_PRETTY_PRINT));
    
    // Resposta simplificada para FlutterFlow
    echo json_encode([
        'success' => true,
        'qr_code_url' => $qrUrl,
        'plant_id' => $plantId,
        'qr_id' => $qrId,
        'structure' => $plantId . '-' . $currentDate,
        'message' => 'QR Code gerado com sucesso'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'qr_code_url' => '',
        'plant_id' => $plantId ?? '',
        'qr_id' => '',
        'structure' => '',
    ]);
}
?>
