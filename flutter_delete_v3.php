<?php
/**
 * API de Deleção de QR Codes - Versão 3.0
 * Deleta QR code por QR ID
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'])) {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Apenas requisições DELETE ou POST são permitidas'
    ]);
    exit;
}

try {
    // Captura o qr_id de diferentes fontes
    $qrId = null;
    
    // 1. Tenta POST form data primeiro
    if (isset($_POST['qr_id']) && !empty(trim($_POST['qr_id']))) {
        $qrId = trim($_POST['qr_id']);
    }
    
    // 2. Tenta JSON body se não encontrou no POST
    if (!$qrId) {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        if ($data && isset($data['qr_id']) && !empty(trim($data['qr_id']))) {
            $qrId = trim($data['qr_id']);
        }
    }
    
    // Validação
    if (!$qrId) {
        throw new Exception('Campo qr_id é obrigatório');
    }
    
    $registryFile = 'data/qr_registry_v3.json';
    
    if (!file_exists($registryFile)) {
        throw new Exception('Arquivo de registro não encontrado');
    }
    
    // Carrega registros existentes
    $content = file_get_contents($registryFile);
    $registries = json_decode($content, true);
    
    if (empty($registries)) {
        throw new Exception('Nenhum registro encontrado');
    }
    
    // Encontra o registro para deletar
    $foundIndex = -1;
    $qrToDelete = null;
    
    foreach ($registries as $index => $registro) {
        if ($registro['qr_id'] === $qrId) {
            $foundIndex = $index;
            $qrToDelete = $registro;
            break;
        }
    }
    
    if ($foundIndex === -1) {
        throw new Exception('QR Code não encontrado: ' . $qrId);
    }
    
    // Deleta o arquivo SVG se existir
    $fileDeleted = false;
    if (file_exists($qrToDelete['filepath'])) {
        $fileDeleted = unlink($qrToDelete['filepath']);
    }
    
    // Remove do array de registros
    array_splice($registries, $foundIndex, 1);
    
    // Salva o arquivo atualizado
    file_put_contents($registryFile, json_encode($registries, JSON_PRETTY_PRINT));
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'QR Code deletado com sucesso',
        'deleted_qr_id' => $qrId,
        'deleted_plant_id' => $qrToDelete['plant_id'],
        'file_deleted' => $fileDeleted,
        'remaining_count' => count($registries),
        'deleted_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'deleted_qr_id' => $qrId ?? null,
        'deleted_at' => date('Y-m-d H:i:s')
    ]);
}
?>
