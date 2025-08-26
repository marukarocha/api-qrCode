<?php
/**
 * API de Listagem de QR Codes - Versão 3.0
 * Compatible com estrutura IdPlant-data
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Apenas requisições GET são permitidas',
        'qr_codes' => []
    ]);
    exit;
}

try {
    $registryFile = 'data/qr_registry_v3.json';
    
    if (!file_exists($registryFile)) {
        echo json_encode([
            'success' => true,
            'message' => 'Nenhum QR code encontrado',
            'qr_codes' => [],
            'total' => 0,
            'structure_info' => 'Format: PlantId-YYYYMMDD-hash'
        ]);
        exit;
    }
    
    $content = file_get_contents($registryFile);
    $registries = json_decode($content, true);
    
    if (empty($registries)) {
        echo json_encode([
            'success' => true,
            'message' => 'Nenhum QR code encontrado',
            'qr_codes' => [],
            'total' => 0,
            'structure_info' => 'Format: PlantId-YYYYMMDD-hash'
        ]);
        exit;
    }
    
    // Ordena por timestamp (mais recente primeiro)
    usort($registries, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    
    // Formata dados para listagem
    $qrCodes = [];
    foreach ($registries as $registro) {
        $qrCodes[] = [
            'plant_id' => $registro['plant_id'],
            'qr_id' => $registro['qr_id'],
            'structure' => $registro['plant_id'] . '-' . $registro['date_part'],
            'qr_code_url' => $registro['qr_code_url'],
            'filename' => $registro['filename'],
            'created_at' => $registro['created_at'],
            'date_part' => $registro['date_part'],
            'hash' => $registro['hash'],
            'size_kb' => round($registro['size_bytes'] / 1024, 2)
        ];
    }
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'QR codes listados com sucesso',
        'qr_codes' => $qrCodes,
        'total' => count($qrCodes),
        'structure_info' => 'Format: PlantId-YYYYMMDD-hash',
        'generated_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'qr_codes' => [],
        'total' => 0
    ]);
}
?>
