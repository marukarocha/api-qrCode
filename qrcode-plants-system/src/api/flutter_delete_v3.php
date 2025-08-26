<?php
// This file handles the deletion of QR codes based on the provided QR ID.

header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../classes/DatabaseManager.php';

$database = new DatabaseManager();
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['qr_id'])) {
        $qrId = $data['qr_id'];
        
        if ($database->deleteQRCode($qrId)) {
            $response['success'] = true;
            $response['message'] = 'QR Code deleted successfully.';
        } else {
            $response['message'] = 'Failed to delete QR Code. It may not exist.';
        }
    } else {
        $response['message'] = 'QR ID is required.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>