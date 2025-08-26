<?php
// File: /qrcode-plants-system/qrcode-plants-system/src/api/flutter_list_v3.php

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../classes/DatabaseManager.php';

$dbManager = new DatabaseManager();

try {
    $qrCodes = $dbManager->getAllQRCodes();
    echo json_encode(['success' => true, 'qr_codes' => $qrCodes]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>