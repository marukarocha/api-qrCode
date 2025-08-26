<?php
// This file handles the API requests for generating QR codes.
// It processes incoming data and returns the generated QR code information.

header('Content-Type: application/json');

require_once '../classes/QRCodeGenerator.php';
require_once '../classes/DatabaseManager.php';

$databaseManager = new DatabaseManager();
$qrCodeGenerator = new QRCodeGenerator();

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['plant_id']) && isset($input['format'])) {
        $plantId = $input['plant_id'];
        $format = $input['format'];

        // Generate QR Code
        $qrCodeData = $qrCodeGenerator->generate($plantId, $format);
        
        if ($qrCodeData) {
            // Save QR Code information to the database
            $databaseManager->saveQRCode($plantId, $qrCodeData['qr_id'], $qrCodeData['structure'], $qrCodeData['created_at']);
            
            echo json_encode([
                'success' => true,
                'qr_code_url' => $qrCodeData['url'],
                'plant_id' => $plantId,
                'qr_id' => $qrCodeData['qr_id'],
                'structure' => $qrCodeData['structure'],
                'created_at' => $qrCodeData['created_at']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao gerar QR Code']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?>