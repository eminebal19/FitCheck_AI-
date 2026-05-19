<?php
// ölçüleri veritabanına kaydeder

$host = 'localhost';
$dbname = 'fitcheck_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);
    
    $full_name = $_POST['full_name'] ?? '';
    $gender = $_POST['gender'] ?? null;
    $height = $_POST['height'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $chest_cm = $_POST['chest_cm'] ?? null;
    $waist_cm = $_POST['waist_cm'] ?? null;
    $hip_cm = $_POST['hip_cm'] ?? null;
    $shoulder_cm = $_POST['shoulder_cm'] ?? null;
    
    // aynı isim var mı kontrol et
    $check = $pdo->prepare("SELECT id FROM user_profiles WHERE full_name = ?");
    $check->execute([$full_name]);
    
    if ($check->rowCount() > 0) {
        // güncelle
        $sql = "UPDATE user_profiles SET gender=?, height=?, weight=?, chest_cm=?, waist_cm=?, hip_cm=?, shoulder_cm=? WHERE full_name=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$gender, $height, $weight, $chest_cm, $waist_cm, $hip_cm, $shoulder_cm, $full_name]);
        echo "Güncellendi";
    } else {
        // yeni kayıt
        $sql = "INSERT INTO user_profiles (full_name, gender, height, weight, chest_cm, waist_cm, hip_cm, shoulder_cm) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$full_name, $gender, $height, $weight, $chest_cm, $waist_cm, $hip_cm, $shoulder_cm]);
        echo "Kaydedildi";
    }
    
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>