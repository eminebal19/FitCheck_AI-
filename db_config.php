<?php
// veritabanı bilgileri
$host = "localhost";
$db = "fitcheck_db";
$user = "root";
$pass = "";

try {
    // bağlantıyı kur
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // hata modunu aç
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // hata varsa durdur
    die("Connection failed: " . $e->getMessage());
}

// groq api anahtarı 
$groq_api_key = "gsk_YOUR_GROQ_API_KEY";
?>