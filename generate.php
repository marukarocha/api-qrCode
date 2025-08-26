<?php
// Habilita a exibição de erros para depuração (remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define o tipo de conteúdo como JSON
header('Content-Type: application/json');

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Apenas requisições POST são permitidas.'
    ]);
    exit;
}

// Inclui o autoloader do Composer
require_once __DIR__ . '/vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

try {
    // Lê o corpo da requisição JSON
    $json = file_get_contents('php://input');
    $data_input = json_decode($json, true);
    
    // Valida se o JSON foi decodificado corretamente
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON inválido: ' . json_last_error_msg());
    }
    
    // Valida se o campo 'data' existe e não está vazio
    if (!isset($data_input['data']) || empty(trim($data_input['data']))) {
        throw new Exception('O campo "data" é obrigatório e não pode estar vazio.');
    }
    
    $data = trim($data_input['data']);
    
    // Cria o diretório para salvar os QR codes, se não existir
    $qrDir = 'qrcodes';
    if (!file_exists($qrDir)) {
        if (!mkdir($qrDir, 0755, true)) {
            throw new Exception('Não foi possível criar o diretório para salvar os QR codes.');
        }
    }
    
    // Gera um ID único para rastreamento
    $uniqueId = uniqid('qr_', true);
    $timestamp = time();
    $dataHash = substr(md5($data), 0, 8);
    
    // Gera um nome único para o arquivo
    $filename = $uniqueId . '_' . $timestamp . '_' . $dataHash . '.svg';
    $filePath = $qrDir . '/' . $filename;
    
    // Configurações do QR Code
    $options = new QROptions([
        'version'      => 5,       // Versão do QR Code (1-40)
        'outputType'   => QRCode::OUTPUT_MARKUP_SVG, // Usar SVG em vez de PNG
        'eccLevel'     => QRCode::ECC_L,  // Nível de correção de erro
        'scale'        => 10,      // Escala/tamanho do pixel
        'imageBase64'  => false,   // Não queremos base64
    ]);
    
    // Cria uma instância do QRCode
    $qrcode = new QRCode($options);
    
    // Gera o QR Code como SVG
    $qrImageData = $qrcode->render($data);
    
    // Salva a imagem no arquivo SVG
    if (file_put_contents($filePath, $qrImageData) === false) {
        throw new Exception('Falha ao salvar o arquivo do QR Code.');
    }
    
    // Verifica se o arquivo foi criado com sucesso
    if (!file_exists($filePath)) {
        throw new Exception('Falha ao gerar o arquivo do QR Code.');
    }
    
    // Gera a URL completa para o arquivo
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $baseUrl = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
    $fileUrl = $baseUrl . '/' . $filePath;
    
    // Informações do arquivo
    $fileSize = filesize($filePath);
    $createdAt = date('c'); // ISO 8601 format
    
    // Retorna uma resposta completa para FlutterFlow
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $uniqueId,
            'original_text' => $data,
            'qr_code_url' => $fileUrl,
            'file_info' => [
                'filename' => $filename,
                'size' => $fileSize,
                'format' => 'svg',
                'created_at' => $createdAt,
                'timestamp' => $timestamp
            ],
            'api_info' => [
                'version' => '1.0',
                'generated_by' => 'chillerlan/php-qrcode',
                'base_url' => $baseUrl
            ]
        ],
        'message' => 'QR Code gerado com sucesso!',
        'status_code' => 200
    ]);
    
} catch (Exception $e) {
    // Em caso de erro, retorna uma resposta de erro
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}