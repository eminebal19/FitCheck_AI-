<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitCheck AI - Akıllı Beden Asistanı</title>
    <style>
        /* sayfa reset */
        * { box-sizing: border-box; transition: all 0.2s ease; }
        
        /* arka plan rengi */
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%); 
            margin: 0; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
        }
        
        /* ana kutu */
        .chat-container { 
            width: 420px; 
            height: 650px; 
            background: rgba(255, 255, 255, 0.95); 
            border-radius: 24px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.08); 
            display: flex; 
            flex-direction: column; 
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
        }
        
        /* mavi başlık */
        .chat-header { 
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); 
            color: white; 
            padding: 20px; 
            text-align: center; 
            font-weight: 600; 
            font-size: 1.25rem; 
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        
        /* küçük etiket */
        .chat-header span {
            font-size: 0.8rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 3px 8px;
            border-radius: 12px;
        }
        
        /* mesaj alanı */
        .chat-box { 
            flex: 1; 
            padding: 24px; 
            overflow-y: auto; 
            display: flex; 
            flex-direction: column; 
            gap: 14px;
            background: #fafbfc;
        }
        
        /* scrollbar süsü */
        .chat-box::-webkit-scrollbar { width: 6px; }
        .chat-box::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        
        /* ortak balon */
        .message { 
            max-width: 85%; 
            padding: 12px 18px; 
            border-radius: 20px; 
            font-size: 0.92rem; 
            line-height: 1.5; 
            word-wrap: break-word;
        }
        
        /* bot balonu */
        .bot { 
            background: white; 
            color: #334155;
            align-self: flex-start; 
            border-top-left-radius: 4px;
            border: 1px solid #f1f5f9;
        }
        
        /* kullanıcı balonu */
        .user { 
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); 
            color: white; 
            align-self: flex-end; 
            border-top-right-radius: 4px;
        }
        
        /* alt bar */
        .input-area { 
            border-top: 1px solid #f1f5f9; 
            padding: 16px; 
            display: flex; 
            gap: 10px; 
            background: white; 
        }
        
        /* mesaj inputu */
        .user-input { 
            flex: 1; 
            border: 1px solid #e2e8f0; 
            padding: 12px 20px; 
            border-radius: 30px; 
            outline: none; 
            background: #f8fafc;
        }
        
        /* tıklanınca parlama */
        .user-input:focus {
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        /* gönder butonu */
        .send-btn { 
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); 
            color: white; 
            border: none; 
            width: 44px; 
            height: 44px; 
            border-radius: 50%; 
            cursor: pointer; 
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .send-btn:hover { transform: scale(1.05); }
        
        /* yazıyor efekti */
        .typing { 
            opacity: 0.7; 
            font-style: italic; 
            background: #f1f5f9;
            color: #64748b;
            border: none;
        }
    </style>
</head>
<body>
<div class="chat-container">
    <div class="chat-header">👕 FitCheck AI <span>Uzman</span></div>
    <div class="chat-box" id="chatBox">
        <div class="message bot">Merhaba! 👋 Ben FitCheck AI. Sana en uygun bedeni bulmamı ister misin? Başlamak için adın ne?</div>
    </div>
    <div class="input-area">
        <input type="text" class="user-input" id="userInput" placeholder="Buraya yazın..." autocomplete="off">
        <button class="send-btn" id="sendBtn">➤</button>
    </div>
</div>

<script>
    // elementleri seç
    const chatBox = document.getElementById('chatBox');
    const userInput = document.getElementById('userInput');
    const sendBtn = document.getElementById('sendBtn');
    
    // çift tıklama engeli
    let waiting = false;
    
    // bot hafızası
    let chatHistory = [
        { role: "assistant", content: "Merhaba! 👋 Ben FitCheck AI. Sana en uygun bedeni bulmamı ister misin? Başlamak için adın ne?" }
    ];

    // ekrana balon ekle
    function addMessage(text, isUser = false) {
        const div = document.createElement('div');
        div.className = 'message ' + (isUser ? 'user' : 'bot');
        div.innerHTML = text.replace(/\n/g, '<br>');
        chatBox.appendChild(div);
        chatBox.scrollTop = chatBox.scrollHeight; // otomatik aşağı kaydır
    }

    // yazıyor yazısı aç
    function showTyping() {
        const typing = document.createElement('div');
        typing.className = 'message bot typing';
        typing.id = 'typing';
        typing.innerHTML = '✍️ Terziniz düşünüyor...';
        chatBox.appendChild(typing);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // yazıyor yazısı sil
    function hideTyping() {
        const el = document.getElementById('typing');
        if (el) el.remove();
    }

    // ana fonksiyon
    async function processInput() {
        const text = userInput.value.trim();
        if (!text || waiting) return; // boşsa dur

        waiting = true; // kilitle
        addMessage(text, true); // ekrana yaz
        userInput.value = ''; // kutuyu temizle
        showTyping();

        chatHistory.push({ role: "user", content: text }); // geçmişe at

        try {
            // api'ye fırlat
            const response = await fetch('ai_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ history: chatHistory })
            });

            const result = await response.json();
            hideTyping();
            
            addMessage(result.reply); // cevabı bas
            chatHistory.push({ role: "assistant", content: result.reply }); // hafızaya al

        } catch (err) {
            hideTyping();
            console.error('Hata:', err);
            addMessage('Bir bağlantı sorunu oluştu, lütfen tekrar deneyin.');
        }

        waiting = false; // kilidi aç
    }

    // tıklama dinle
    sendBtn.addEventListener('click', processInput);
    
    // enter dinle
    userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') processInput();
    });
</script>
</body>
</html>