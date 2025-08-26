# QR Code Plants System

## Overview
The QR Code Plants System is a web application designed to generate, manage, and retrieve QR codes associated with plant IDs. This project provides a user-friendly interface for creating QR codes, listing existing codes, and searching for specific codes.

## Project Structure
```
qrcode-plants-system
├── src
│   ├── api
│   │   ├── flutter_api_v3.php       # Handles API requests for generating QR codes
│   │   ├── flutter_list_v3.php      # Retrieves and returns a list of generated QR codes
│   │   └── flutter_delete_v3.php    # Handles deletion of QR codes based on QR ID
│   ├── assets
│   │   ├── css
│   │   │   └── styles.css            # Contains CSS styles for the application
│   │   └── js
│   │       └── app.js                # Contains JavaScript code for client-side interactions
│   ├── config
│   │   └── database.php              # Contains database connection settings
│   ├── classes
│   │   ├── QRCodeGenerator.php       # Defines the QRCodeGenerator class
│   │   └── DatabaseManager.php        # Defines the DatabaseManager class
│   └── qr_codes                      # Directory for storing generated QR code images
├── index.php                         # Entry point of the application
├── composer.json                     # Composer configuration file
└── README.md                         # Project documentation
```

## Installation
1. Clone the repository:
   ```
   git clone <repository-url>
   ```
2. Navigate to the project directory:
   ```
   cd qrcode-plants-system
   ```
3. Install dependencies using Composer:
   ```
   composer install
   ```

## Usage
- Open `index.php` in your web browser to access the application.
- Use the "Gerar QR" tab to create new QR codes.
- Use the "Listar QRs" tab to view all generated QR codes.
- Use the "Buscar" tab to search for specific QR codes by plant ID or QR ID.

## Contributing
Contributions are welcome! Please submit a pull request or open an issue for any enhancements or bug fixes.

## License
This project is licensed under the MIT License. See the LICENSE file for more details.