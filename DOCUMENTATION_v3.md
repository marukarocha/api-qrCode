# Sistema de QR Code - Documentação das APIs v3.0

## Estrutura do Sistema
- **Formato:** `PlantId-YYYYMMDD-hash8chars`
- **Exemplo:** `PLT001-20250821-abc12345`
- **Compatibilidade:** FlutterFlow e Interface Web

## APIs Disponíveis

### 1. Geração de QR Code
**Endpoint:** `https://qrcode.seedapp.dev/flutter_api_v3.php`
**Método:** `POST`
**Content-Type:** `application/json`

#### Request Body:
```json
{
    "plant_id": "PLT001"
}
```

#### Response Success:
```json
{
    "success": true,
    "message": "QR Code gerado com sucesso",
    "qr_code_url": "https://qrcode.seedapp.dev/qrcodes/PLT001-20250821-abc12345.svg",
    "plant_id": "PLT001",
    "qr_id": "PLT001-20250821-abc12345",
    "filename": "PLT001-20250821-abc12345.svg",
    "created_at": "2025-08-21 17:15:48",
    "structure": "PLT001-20250821",
    "hash": "abc12345"
}
```

#### Response Error:
```json
{
    "success": false,
    "message": "Campo plant_id é obrigatório",
    "qr_code_url": null,
    "plant_id": null,
    "qr_id": null,
    "created_at": "2025-08-21 17:15:48"
}
```

### 2. Listagem de QR Codes
**Endpoint:** `https://qrcode.seedapp.dev/flutter_list_v3.php`
**Método:** `GET`

#### Response Success:
```json
{
    "success": true,
    "message": "QR codes listados com sucesso",
    "qr_codes": [
        {
            "plant_id": "PLT001",
            "qr_id": "PLT001-20250821-abc12345",
            "structure": "PLT001-20250821",
            "qr_code_url": "https://qrcode.seedapp.dev/qrcodes/PLT001-20250821-abc12345.svg",
            "filename": "PLT001-20250821-abc12345.svg",
            "created_at": "2025-08-21 17:15:48",
            "date_part": "20250821",
            "hash": "abc12345",
            "size_kb": 15.04
        }
    ],
    "total": 1,
    "structure_info": "Format: PlantId-YYYYMMDD-hash",
    "generated_at": "2025-08-21 17:16:18"
}
```

### 3. Deleção de QR Code
**Endpoint:** `https://qrcode.seedapp.dev/flutter_delete_v3.php`
**Método:** `POST` ou `DELETE`
**Content-Type:** `application/json`

#### Request Body:
```json
{
    "qr_id": "PLT001-20250821-abc12345"
}
```

#### Response Success:
```json
{
    "success": true,
    "message": "QR Code deletado com sucesso",
    "deleted_qr_id": "PLT001-20250821-abc12345",
    "deleted_plant_id": "PLT001",
    "file_deleted": true,
    "remaining_count": 0,
    "deleted_at": "2025-08-21 17:20:00"
}
```

#### Response Error:
```json
{
    "success": false,
    "message": "QR Code não encontrado: PLT001-20250821-abc12345",
    "deleted_qr_id": "PLT001-20250821-abc12345",
    "deleted_at": "2025-08-21 17:20:00"
}
```

## Interface Web

### Acesso
**URL:** `https://qrcode.seedapp.dev/index_v3.php`

### Funcionalidades
1. **Gerar QR:** Criar novos QR codes usando Plant ID
2. **Listar QRs:** Ver todos os QR codes gerados
3. **Buscar:** Filtrar QR codes por Plant ID
4. **Deletar:** Remover QR codes individuais ou múltiplos
5. **Copiar URL:** Copiar link do QR code para área de transferência
6. **Download:** Baixar arquivo SVG

### Recursos da Interface
- ✅ Design responsivo com Materialize CSS
- ✅ Seleção múltipla para deleção em lote
- ✅ Busca em tempo real
- ✅ Confirmação antes de deletar
- ✅ Feedback visual com toasts
- ✅ Preview dos QR codes gerados

## Para FlutterFlow

### Custom Data Type: QRCodeResponse
```dart
class QRCodeResponse {
  bool success;
  String message;
  String qrCodeUrl;
  String plantId;
  String qrId;
  String createdAt;
  String structure;
  String hash;
}
```

### Custom Action: generatePlantQRCode
```dart
Future<QRCodeResponse> generatePlantQRCode(String plantId) async {
  final response = await http.post(
    Uri.parse('https://qrcode.seedapp.dev/flutter_api_v3.php'),
    headers: {'Content-Type': 'application/json'},
    body: json.encode({'plant_id': plantId}),
  );
  
  final data = json.decode(response.body);
  return QRCodeResponse.fromJson(data);
}
```

### Custom Action: deleteQRCode
```dart
Future<Map<String, dynamic>> deleteQRCode(String qrId) async {
  final response = await http.post(
    Uri.parse('https://qrcode.seedapp.dev/flutter_delete_v3.php'),
    headers: {'Content-Type': 'application/json'},
    body: json.encode({'qr_id': qrId}),
  );
  
  return json.decode(response.body);
}
```

### Custom Action: listQRCodes
```dart
Future<List<QRCode>> listQRCodes() async {
  final response = await http.get(
    Uri.parse('https://qrcode.seedapp.dev/flutter_list_v3.php'),
  );
  
  final data = json.decode(response.body);
  if (data['success']) {
    return (data['qr_codes'] as List)
        .map((qr) => QRCode.fromJson(qr))
        .toList();
  }
  return [];
}
```

## Códigos de Status HTTP
- **200:** Sucesso
- **405:** Método não permitido
- **500:** Erro interno do servidor

## Formato dos Arquivos
- **Tipo:** SVG (Scalable Vector Graphics)
- **Tamanho médio:** ~15KB
- **Resolução:** Vetorial (escalável)
- **Compatibilidade:** Todos os navegadores e apps modernos

## Estrutura de Arquivos no Servidor
```
/qrcodes/               # Arquivos SVG dos QR codes
/data/                  # Arquivo de registro JSON
  qr_registry_v3.json   # Base de dados dos QR codes
```

## Exemplos de Plant IDs Válidos
- `PLT001`, `PLT002`, `PLT003`
- `PLANT_A`, `PLANT_B`
- `TREE001`, `FLOWER123`
- `HERB_BASIL`, `ROSE_RED`

## Observações Importantes
1. O Plant ID é case-sensitive
2. Cada geração cria um hash único
3. A data é sempre no formato YYYYMMDD
4. Arquivos SVG são otimizados para web e mobile
5. A deleção remove tanto o arquivo quanto o registro

---

**Última atualização:** 21 de agosto de 2025
**Versão:** 3.0
**Status:** Produção
