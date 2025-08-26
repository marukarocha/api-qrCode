<?php
class DatabaseManager {
    private $connection;

    public function __construct($host, $db, $user, $pass) {
        $this->connection = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function insertQRCode($plantId, $structure, $qrId, $createdAt, $sizeKb, $qrCodeUrl) {
        $stmt = $this->connection->prepare("INSERT INTO qr_codes (plant_id, structure, qr_id, created_at, size_kb, qr_code_url) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$plantId, $structure, $qrId, $createdAt, $sizeKb, $qrCodeUrl]);
    }

    public function getQRCodes() {
        $stmt = $this->connection->query("SELECT * FROM qr_codes");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteQRCode($qrId) {
        $stmt = $this->connection->prepare("DELETE FROM qr_codes WHERE qr_id = ?");
        return $stmt->execute([$qrId]);
    }

    public function __destruct() {
        $this->connection = null;
    }
}
?>