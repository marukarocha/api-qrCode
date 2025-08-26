<?php
// API para buscar QR codes por ID
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $qrDir = 'qrcodes';
    
    // Verifica se o método é GET para busca
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        // Se tem ID na query string, busca específico
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $searchId = trim($_GET['id']);
            
            // Busca arquivos que começam com esse ID
            $pattern = $qrDir . '/' . $searchId . '_*';
            $files = glob($pattern);
            
            if (empty($files)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'QR Code não encontrado para o ID: ' . $searchId,
                    'status_code' => 404
                ]);
                http_response_code(404);
                exit;
            }
            
            $file = $files[0]; // Pega o primeiro encontrado
            $filename = basename($file);
            
            // Extrai informações do nome do arquivo
            $parts = explode('_', str_replace('.svg', '', $filename));
            $id = $parts[0] . '_' . $parts[1];
            $timestamp = isset($parts[2]) ? $parts[2] : 0;
            
            // Gera URL completa
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
            $baseUrl = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
            $fileUrl = $baseUrl . '/' . $file;
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $searchId,
                    'qr_code_url' => $fileUrl,
                    'file_info' => [
                        'filename' => $filename,
                        'size' => filesize($file),
                        'format' => 'svg',
                        'created_at' => date('c', $timestamp),
                        'timestamp' => $timestamp
                    ]
                ],
                'message' => 'QR Code encontrado!',
                'status_code' => 200
            ]);
            
        } else {
            // Lista todos os QR codes
            $files = glob($qrDir . '/*.svg');
            $qrCodes = [];
            
            foreach ($files as $file) {
                $filename = basename($file);
                $parts = explode('_', str_replace('.svg', '', $filename));
                $id = $parts[0] . '_' . $parts[1];
                $timestamp = isset($parts[2]) ? $parts[2] : 0;
                
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                $baseUrl = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
                $fileUrl = $baseUrl . '/' . $file;
                
                $qrCodes[] = [
                    'id' => $id,
                    'qr_code_url' => $fileUrl,
                    'filename' => $filename,
                    'size' => filesize($file),
                    'created_at' => date('c', $timestamp),
                    'timestamp' => $timestamp
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'qr_codes' => $qrCodes,
                    'total' => count($qrCodes)
                ],
                'message' => 'Lista de QR codes encontrada!',
                'status_code' => 200
            ]);
        }
        
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Apenas métodos GET são permitidos para busca.',
            'status_code' => 405
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'status_code' => 500
    ]);
}
