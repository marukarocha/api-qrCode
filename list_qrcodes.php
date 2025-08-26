<?php
/**
 * API para listar QR codes gerados
 * Endpoint simplificado para FlutterFlow
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Apenas GET permitido
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Apenas GET é permitido',
        'data' => []
    ]);
    exit;
}

try {
    $qrDir = __DIR__ . '/qrcodes';
    
    // Verifica se diretório existe
    if (!is_dir($qrDir)) {
        echo json_encode([
            'success' => true,
            'message' => 'Nenhum QR code encontrado',
            'data' => [],
            'total' => 0
        ]);
        exit;
    }
    
    // Lista arquivos SVG
    $pattern = $qrDir . '/*.svg';
    $files = glob($pattern);
    
    if (empty($files)) {
        echo json_encode([
            'success' => true,
            'message' => 'Nenhum QR code encontrado',
            'data' => [],
            'total' => 0
        ]);
        exit;
    }
    
    $qrCodes = [];
    $baseUrl = 'https://qrcode.seedapp.dev';
    
    foreach ($files as $filepath) {
        $filename = basename($filepath);
        
        // Extrai informações do nome do arquivo
        // Formato: plant_xxxxx_timestamp.svg ou qr_PLTxxx_timestamp_uniqueid.svg
        $info = pathinfo($filename);
        $nameWithoutExt = $info['filename'];
        
        // Tenta diferentes padrões de extração
        $plantId = 'N/A';
        $qrId = $nameWithoutExt;
        $timestamp = 0;
        
        // Padrão 1: plant_ID_timestamp
        if (preg_match('/plant_(.+?)_(\d+)/', $nameWithoutExt, $matches)) {
            $rawId = $matches[1];
            $timestamp = (int)$matches[2];
            
            // Se o ID parece um hash, tenta encontrar PLT no conteúdo
            if (strlen($rawId) > 15 || strpos($rawId, '.') !== false) {
                // Lê o conteúdo do arquivo SVG para encontrar o Plant ID real
                $svgContent = file_get_contents($filepath);
                if (preg_match('/PLT\d+/i', $svgContent, $contentMatch)) {
                    $plantId = strtoupper($contentMatch[0]);
                } else {
                    $plantId = 'PLT' . substr(md5($rawId), 0, 3); // ID derivado
                }
            } else {
                $plantId = $rawId;
            }
        } 
        // Padrão 2: qr_PLTxxx_timestamp_uniqueid
        elseif (preg_match('/qr_(.+?)_(\d{8})_(.+)/', $nameWithoutExt, $matches)) {
            $plantId = $matches[1];
            $timestamp = filemtime($filepath);
            $qrId = $matches[1] . '_' . $matches[2] . '_' . $matches[3];
        } 
        // Padrão 3: Busca PLT diretamente no nome
        elseif (preg_match('/(PLT\d+)/i', $nameWithoutExt, $matches)) {
            $plantId = strtoupper($matches[1]);
            $timestamp = filemtime($filepath);
        }
        // Padrão 4: Fallback - lê o conteúdo do arquivo
        else {
            $timestamp = filemtime($filepath);
            // Tenta ler o conteúdo do SVG
            $svgContent = file_get_contents($filepath);
            if (preg_match('/PLT\d+/i', $svgContent, $contentMatch)) {
                $plantId = strtoupper($contentMatch[0]);
            } else {
                // Usa parte do filename como ID
                $plantId = 'ID_' . substr($nameWithoutExt, 0, 8);
            }
        }
        
        // Se não conseguiu extrair timestamp, usa o do arquivo
        if ($timestamp == 0) {
            $timestamp = filemtime($filepath);
        }
        
        $qrCodes[] = [
            'plant_id' => $plantId ?: 'N/A',
            'qr_id' => $qrId,
            'filename' => $filename,
            'qr_code_url' => $baseUrl . '/qrcodes/' . $filename,
            'size' => filesize($filepath),
            'created_at' => date('Y-m-d H:i:s', $timestamp),
            'timestamp' => $timestamp
        ];
    }
    
    // Ordena por timestamp (mais recente primeiro)
    usort($qrCodes, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    
    // Resposta final
    echo json_encode([
        'success' => true,
        'message' => 'QR codes listados com sucesso',
        'data' => $qrCodes,
        'total' => count($qrCodes),
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage(),
        'data' => [],
        'total' => 0
    ]);
}
?>
