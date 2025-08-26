<?php
/**
 * Sistema de Listagem Multi-Tipos - Versão 4.0
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $registryFile = 'data/qr_registry_v4.json';
    
    if (!file_exists($registryFile)) {
        echo json_encode([
            'success' => true,
            'message' => 'Nenhum QR Code encontrado',
            'total' => 0,
            'by_type' => [],
            'qr_codes' => []
        ]);
        exit;
    }
    
    $registries = json_decode(file_get_contents($registryFile), true) ?: [];
    
    // Filtro por tipo (opcional)
    $filterType = $_GET['type'] ?? null;
    if ($filterType) {
        $registries = array_filter($registries, function($qr) use ($filterType) {
            return strtoupper($qr['type']) === strtoupper($filterType);
        });
    }
    
    // Estatísticas por tipo
    $byType = [];
    foreach ($registries as $qr) {
        $type = $qr['type'];
        if (!isset($byType[$type])) {
            $byType[$type] = [
                'type' => $type,
                'name' => $qr['type_name'],
                'count' => 0,
                'latest' => null
            ];
        }
        $byType[$type]['count']++;
        if (!$byType[$type]['latest'] || $qr['created_at'] > $byType[$type]['latest']) {
            $byType[$type]['latest'] = $qr['created_at'];
        }
    }
    
    // Ordenar por data (mais recente primeiro)
    usort($registries, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    echo json_encode([
        'success' => true,
        'message' => 'QR Codes listados com sucesso',
        'total' => count($registries),
        'by_type' => array_values($byType),
        'filter_applied' => $filterType ? "Filtrado por: $filterType" : "Todos os tipos",
        'qr_codes' => $registries
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar QR Codes: ' . $e->getMessage(),
        'total' => 0,
        'qr_codes' => []
    ]);
}
?>

<script>
// Gerar QR Code para Planta (compatível com versão anterior)
fetch('/flutter_api_v4.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        type: 'PLANT',
        item_id: 'PLT001',
        species: 'Rosa Vermelha',
        location: 'Jardim A',
        format: 'png'
    })
});

// Gerar QR Code para Produto
fetch('/flutter_api_v4.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        type: 'PRODUCT',
        item_id: 'PROD001',
        category: 'Eletrônicos',
        brand: 'Samsung',
        price: '899.99'
    })
});

// Gerar QR Code para Equipamento
fetch('/flutter_api_v4.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        type: 'EQUIPMENT',
        item_id: 'EQP001',
        model: 'Impressora HP',
        serial: 'HP123456',
        location: 'Escritório 2'
    })
});

// Listar apenas plantas
fetch('/flutter_list_v4.php?type=PLANT');

// Listar todos os tipos
fetch('/flutter_list_v4.php');
</script>