

// --- SADECE BU BLOĞU AI_HANDLER.PHP İÇİNE UYGUN YERE YAPIŞTIR ---

// $reply: Groq'tan gelen ham metin yanıt değişkenin. Eğer senin kodunda adı farklıysa (örn: $bot_response), buraları onunla değiştir.
if (strpos($reply, 'DATA_EXPORT|') !== false && isset($pdo)) {
    
    // Şifreli satırı kullanıcının göreceği metinden ayırıyoruz
    $parts = explode('DATA_EXPORT|', $reply);
    $replyClean = trim($parts[0]); // Kullanıcıya döneceğin temiz mesaj
    $dataLine = trim($parts[1]);   // Veritabanına gidecek ham veri şeridi
    
    // Verileri parçalayıp diziye alıyoruz
    $dataPairs = explode('|', $dataLine);
    $parsedData = [];
    foreach ($dataPairs as $pair) {
        if (strpos($pair, ':') !== false) {
            list($key, $value) = explode(':', $pair);
            $parsedData[$key] = trim($value);
        }
    }
    
    // user_profiles tablosuna güvenli UPDATE işlemi
    try {
        $query = "UPDATE user_profiles SET 
                    full_name = :full_name, 
                    gender = :gender, 
                    height = :height, 
                    weight = :weight, 
                    chest_cm = :chest_cm, 
                    waist_cm = :waist_cm, 
                    hip_cm = :hip_cm, 
                    shoulder_cm = :shoulder_cm 
                  WHERE id = :id";
                  
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':full_name'   => $parsedData['full_name'] ?? null,
            ':gender'      => (isset($parsedData['gender']) && ($parsedData['gender'] == 'kadın' || $parsedData['gender'] == 'erkek')) ? $parsedData['gender'] : null,
            ':height'      => isset($parsedData['height']) ? (int)$parsedData['height'] : null,
            ':weight'      => isset($parsedData['weight']) ? (int)$parsedData['weight'] : null,
            ':chest_cm'    => isset($parsedData['chest_cm']) ? (int)$parsedData['chest_cm'] : null,
            ':waist_cm'    => isset($parsedData['waist_cm']) ? (int)$parsedData['waist_cm'] : null,
            ':hip_cm'      => isset($parsedData['hip_cm']) ? (int)$parsedData['hip_cm'] : null,
            ':shoulder_cm' => isset($parsedData['shoulder_cm']) ? (int)$parsedData['shoulder_cm'] : null,
            ':id'          => $profileId // Frontend'den gelen ya da session'daki kullanıcı ID'si
        ]);
        
        // En son kullanıcıya göndereceğin yanıtı temizlenmiş haliyle güncelliyorsun
        $reply = $replyClean;
        
    } catch (PDOException $e) {
        // Hata durumunda session veya log tutabilirsin, hackathon'da akış durmasın diye boş bırakıldı
    }
}