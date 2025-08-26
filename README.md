# 🎯 QR Code Generator API - FlutterFlow Ready

Uma API moderna e completa para geração de QR Codes, otimizada para integração com FlutterFlow e outras aplicações.

## 🚀 Funcionalidades

- ✅ **Geração de QR Codes** com IDs únicos rastreáveis
- ✅ **Busca por ID** específico
- ✅ **Listagem** de todos os QR Codes gerados
- ✅ **Interface web moderna** com Materialize CSS
- ✅ **JSON estruturado** para FlutterFlow
- ✅ **Logo customizada** na pasta assets
- ✅ **Saída SVG** (sem dependência da extensão GD)

## 📂 Estrutura do Projeto

```
qrcode/
├── assets/
│   └── logo.svg              # Logo do sistema
├── qrcodes/                  # Diretório dos QR codes gerados
├── vendor/                   # Dependências do Composer
├── index.php                 # Interface web completa
├── generate.php              # API para gerar QR codes
├── search.php                # API para buscar/listar QR codes
├── composer.json             # Configurações do Composer
└── README.md                 # Documentação
```

## 🔗 Endpoints da API

### 1. **Gerar QR Code**
**POST** `/generate.php`

**Request:**
```json
{
    "data": "TEXTO_OU_ID_AQUI"
}
```

**Response de sucesso:**
```json
{
    "success": true,
    "data": {
        "id": "qr_67890abcdef12345.00000000",
        "original_text": "TEXTO_OU_ID_AQUI",
        "qr_code_url": "https://seudominio.com/qrcode/qrcodes/qr_67890abcdef12345.00000000_1755784567_a1b2c3d4.svg",
        "file_info": {
            "filename": "qr_67890abcdef12345.00000000_1755784567_a1b2c3d4.svg",
            "size": 36514,
            "format": "svg",
            "created_at": "2025-08-21T14:30:45+00:00",
            "timestamp": 1755784567
        },
        "api_info": {
            "version": "1.0",
            "generated_by": "chillerlan/php-qrcode",
            "base_url": "https://seudominio.com/qrcode"
        }
    },
    "message": "QR Code gerado com sucesso!",
    "status_code": 200
}
```

### 2. **Buscar QR Code por ID**
**GET** `/search.php?id=QR_ID_AQUI`

**Response de sucesso:**
```json
{
    "success": true,
    "data": {
        "id": "qr_67890abcdef12345",
        "qr_code_url": "https://seudominio.com/qrcode/qrcodes/qr_67890abcdef12345.00000000_1755784567_a1b2c3d4.svg",
        "file_info": {
            "filename": "qr_67890abcdef12345.00000000_1755784567_a1b2c3d4.svg",
            "size": 36514,
            "format": "svg",
            "created_at": "2025-08-21T14:30:45+00:00",
            "timestamp": 1755784567
        }
    },
    "message": "QR Code encontrado!",
    "status_code": 200
}
```

### 3. **Listar todos os QR Codes**
**GET** `/search.php`

**Response de sucesso:**
```json
{
    "success": true,
    "data": {
        "qr_codes": [
            {
                "id": "qr_67890abcdef12345",
                "qr_code_url": "https://seudominio.com/qrcode/qrcodes/qr_67890abcdef12345.00000000_1755784567_a1b2c3d4.svg",
                "filename": "qr_67890abcdef12345.00000000_1755784567_a1b2c3d4.svg",
                "size": 36514,
                "created_at": "2025-08-21T14:30:45+00:00",
                "timestamp": 1755784567
            }
        ],
        "total": 1
    },
    "message": "Lista de QR codes encontrada!",
    "status_code": 200
}
```

## 🎨 Interface Web

Acesse `index.php` para utilizar a interface web completa com 3 abas:

1. **📝 Gerar**: Crie novos QR codes e visualize o JSON de resposta
2. **🔍 Buscar**: Encontre QR codes específicos por ID
3. **📋 Listar**: Visualize todos os QR codes gerados

## 🔧 Instalação

1. **Clone o repositório** para seu servidor web
2. **Instale as dependências** via Composer:
   ```bash
   composer install
   ```
3. **Configure as permissões** das pastas:
   ```bash
   chmod 755 qrcodes/
   chmod 755 assets/
   ```

## 💡 Integração com FlutterFlow

### Exemplo de uso no FlutterFlow:

```dart
// Gerar QR Code
final response = await http.post(
  Uri.parse('https://seudominio.com/qrcode/generate.php'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({'data': 'MEU_TEXTO'}),
);

if (response.statusCode == 200) {
  final data = jsonDecode(response.body);
  if (data['success']) {
    String qrCodeId = data['data']['id'];
    String qrCodeUrl = data['data']['qr_code_url'];
    // Use os dados conforme necessário
  }
}
```

### Buscar QR Code:

```dart
final response = await http.get(
  Uri.parse('https://seudominio.com/qrcode/search.php?id=$qrCodeId'),
);

if (response.statusCode == 200) {
  final data = jsonDecode(response.body);
  if (data['success']) {
    String qrCodeUrl = data['data']['qr_code_url'];
    // Exibir o QR code
  }
}
```

## 📋 Tecnologias Utilizadas

- **PHP 8.2+** 
- **chillerlan/php-qrcode v5.0.3** - Biblioteca moderna de QR codes
- **Materialize CSS** - Framework de UI responsivo
- **Composer** - Gerenciador de dependências

## ⚙️ Configurações

- **Formato de saída**: SVG (suporta PNG com extensão GD)
- **Nível de correção**: L (Low) - pode ser alterado nas opções
- **Escala**: 10px por módulo
- **Versionamento**: Automático (versão 5 padrão)

## 🆔 Sistema de IDs

Os QR codes são salvos com nomes únicos no formato:
```
qr_[UNIQUE_ID]_[TIMESTAMP]_[DATA_HASH].svg
```

Exemplo: `qr_67890abcdef12345.00000000_1755784567_a1b2c3d4.svg`

- **qr_67890abcdef12345.00000000**: ID único gerado pelo PHP
- **1755784567**: Timestamp Unix de criação  
- **a1b2c3d4**: Hash MD5 dos primeiros 8 caracteres do conteúdo

## 🔒 Segurança

- Validação de entrada JSON
- Sanitização de dados
- Nomes de arquivo seguros
- Controle de permissões de diretório
- Headers CORS configurados

---

**🎯 Pronto para produção e integração com FlutterFlow! 🚀**

# api-qrCode
Projeto para de uma api em php que gera qrcode com informações do Flutter e Json 
