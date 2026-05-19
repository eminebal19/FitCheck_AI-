<?php
// hataları kapa, json ayarla
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

// ayarları çek
if (file_exists('db_config.php')) {
    require_once 'db_config.php';
} else {
    echo json_encode(['reply' => 'Hata: db_config.php bulunamadı.', 'history' => []]);
    exit;
}

// chat geçmişini al
$inputData = json_decode(file_get_contents('php://input'), true);
$history = $inputData['history'] ?? [];

// veri yoksa dur
if (empty($history)) {
    echo json_encode(['reply' => "Sohbet başlatılamadı.", 'history' => []]);
    exit;
}

// groq api linki
$url = "https://api.groq.com/openai/v1/chat/completions";

// bota karakter tanımı ve kurallar
$systemMessage = [
    "role" => "system",
    "content" => "Selam, sen FitCheck uygulamasının arkasındaki uzman beden asistanısın. İşin çok basit ama titiz olman lazım: Kullanıcıya en doğru beden önerisini (S, M, L, XL vs.) sunacaksın. Bunun için de bazı kurallarımız var, bunlara sadık kal:\n\n"
             . "1. Sohbet geçmişine iyi bak. Adam zaten adını, boyunu, kilosunu yazdıysa bir daha 'Adınız nedir?' diye sorma, darlamayalım kullanıcıyı.\n"
             . "2. Sadece boy ve kilo alıp hemen 'Sen M bedensin' falan deme. Boy/kilo tek başına işe yaramaz, kesinlikle yanlış yönlendiririz. Acele etme yani.\n"
             . "3. Doğru beden bulmak için şu bilgileri sırayla ve tek tek sor (Aynı mesajda hepsini isteme, her mesajda sadece bir soru sor):\n"
             . "   - İsim (full_name)\n"
             . "   - Cinsiyet (gender: 'kadın' veya 'erkek' olarak belirtmesini sağla)\n"
             . "   - Boy (height - cm) ve Kilo (weight - kg)\n"
             . "   - Göğüs çevresi ölçüsü (chest_cm)\n"
             . "   - Bel çevresi ölçüsü (waist_cm)\n"
             . "   - Kalça çevresi ölçüsü (hip_cm)\n"
             . "   - Omuz genişliği ölçüsü (shoulder_cm)\n"
             . "4. Kullanıcı ölçüleri bilmiyorum derse, hemen moralini bozma. Mezura ile nasıl kolayca ölçeceğini tatlı bir dille tarif et, 'Bilmiyorsan tahmin et' de geç.\n"
             . "5. Yukarıdaki bilgilerin hepsi (Ad, cinsiyet, boy, kilo, göğüs, bel, kalça, omuz) tam olarak elimize geçmeden sakın erkenden beden tahmini yapma.\n"
             . "6. Bütün verileri topladıktan sonra tecrübeli bir terzi gibi davran. Boy/kilo ve göğüs/bel oranlarını güzelce yorumla. Kullanıcıya dar kalıp mı geniş kalıp mı sevdiğini de hesaba katarak şık ve net bir beden önerisi yap. Öneriyi yaptıktan sonra, mesajının EN SONUNA tam olarak şu formatta teknik veri satırını ekle (Kullanıcıya çaktırma ama aynen bu formatta bitir):\n"
             . "DATA_EXPORT|full_name:[İsim]|gender:[erkek/kadın]|height:[Boy]|weight:[Kilo]|chest_cm:[Göğüs]|waist_cm:[Bel]|hip_cm:[Kalça]|shoulder_cm:[Omuz]"
];

// kuralı sohbetin başına göm
array_unshift($history, $systemMessage);

// groq veri paketi
$payload = [
    "model" => "llama-3.1-8b-instant", 
    "messages" => $history,
    "temperature" => 0.5 
];

// curl ile groq'a bağlan
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $groq_api_key
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// isteği gönder ve kapat
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$reply = "Lütfen tekrar deneyiniz.";

// yanıt düzgün geldiyse işle
if ($httpCode === 200 && $response) {
    $result = json_decode($response, true);
    if (isset($result['choices'][0]['message']['content'])) {
        $reply = $result['choices'][0]['message']['content'];
        
        // konuşma bittiyse ve export geldiyse vt'ye yaz
        if (strpos($reply, 'DATA_EXPORT|') !== false && isset($pdo)) {
            
            // gizli şeridi mesajdan ayır
            $parts = explode('DATA_EXPORT|', $reply);
            $replyClean = trim($parts[0]); 
            $dataLine = trim($parts[1]);   
            
            // string veriyi diziye çevir
            $dataPairs = explode('|', $dataLine);
            $parsedData = [];
            foreach ($dataPairs as $pair) {
                if (strpos($pair, ':') !== false) {
                    list($key, $value) = explode(':', $pair);
                    $parsedData[$key] = trim($value);
                }
            }
            
            // vt'ye yeni profil ekle
            try {
                $query = "INSERT INTO user_profiles (full_name, gender, height, weight, chest_cm, waist_cm, hip_cm, shoulder_cm) 
                          VALUES (:full_name, :gender, :height, :weight, :chest_cm, :waist_cm, :hip_cm, :shoulder_cm)";
                          
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    ':full_name'   => $parsedData['full_name'] ?? null,
                    ':gender'      => (isset($parsedData['gender']) && ($parsedData['gender'] == 'kadın' || $parsedData['gender'] == 'erkek')) ? $parsedData['gender'] : null,
                    ':height'      => isset($parsedData['height']) ? (int)$parsedData['height'] : null,
                    ':weight'      => isset($parsedData['weight']) ? (int)$parsedData['weight'] : null,
                    ':chest_cm'    => isset($parsedData['chest_cm']) ? (int)$parsedData['chest_cm'] : null,
                    ':waist_cm'    => isset($parsedData['waist_cm']) ? (int)$parsedData['waist_cm'] : null,
                    ':hip_cm'      => isset($parsedData['hip_cm']) ? (int)$parsedData['hip_cm'] : null,
                    ':shoulder_cm' => isset($parsedData['shoulder_cm']) ? (int)$parsedData['shoulder_cm'] : null
                ]);
                
                // ekrandaki gizli şeridi temizle
                $reply = $replyClean;
                
            } catch (PDOException $e) {
                // patlarsa akış bozulmasın
            }
        } 
    }
}

// json bas ve çık
echo json_encode([
    'reply' => $reply
], JSON_UNESCAPED_UNICODE);
exit;
?>