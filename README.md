# FitCheck AI - Akıllı Beden Asistanı

FitCheck AI, e-ticaret moda sektöründeki en büyük problemlerden biri olan **yanlış beden seçimi** ve buna bağlı **yüksek iade oranlarını** çözmek amacıyla geliştirilmiş, yapay zeka tabanlı akıllı bir beden danışmanıdır. 

Standart ve kafa karıştırıcı beden tabloları yerine, kullanıcıyla tıpkı bir mağazadaki **uzman terzi** gibi doğal bir dille konuşarak en doğru kıyafet bedenini (S, M, L, XL vb.) analiz eder ve önerir.

---

## Temel Özellikler

* **Hafızalı Sohbet Mimarisi (Stateful Chat):** Yapay zeka, konuşma boyunca kullanıcının verdiği Ad, Cinsiyet, Boy ve Kilo gibi bilgileri asla unutmaz. Sohbet geçmişini kümülatif olarak analiz eder.
* **Gelişmiş Vücut Analizi:** Sadece boy ve kiloya bakarak aceleci tahminler yapmak yerine; kullanıcıdan göğüs ve bel gibi kritik terzilik ölçülerini sırayla talep eder.
* **İnsansı ve Doğal Dil Deneyimi:** Robotik ve mekanik kalıplardan uzak, kullanıcıyı motive eden ve yönlendiren samimi bir sohbet tonuna sahiptir.
* **Modern ve Duyarlı (Responsive) Arayüz:** Kullanıcı deneyimini ön planda tutan, şık parlama efektlerine ve akıcı animasyonlara sahip premium chat ekranı.

---
## Gelecek Yol Haritası 
Satıcı Entegrasyonu ve Nokta Atışı Öneri: İlerleyen aşamalarda satıcıların, sattıkları kıyafetlerin kalıp ve santimetre cinsinden detaylı beden ölçülerini (örn: X markasının M bedeni için göğüs/bel ölçüleri) veritabanına girebileceği bir panel eklenecektir. Böylece yapay zeka, kullanıcının ölçüleriyle ürünün gerçek ölçülerini eşleştirerek sıfır hata ile nokta atışı beden tavsiyesi yapabilecektir.

---

## Kullanılan Teknolojiler

* **Frontend:** HTML5, CSS3 (Modern UI/UX, Flexbox, Glassmorphism, CSS Animations), JavaScript (Asenkron Fetch API, State Management)
* **Backend:** PHP 8.x (cURL API Entegrasyonu, JSON Veri Yönetimi)
* **Yapay Zeka (AI):** Groq API (Llama 3.1 8B Instant Modeli - Prompt Engineering & System Instructions)
* **Yerel Sunucu:** XAMPP

---

## Kurulum ve Çalıştırma

Projeyi yerel bilgisayarınızda çalıştırmak için aşağıdaki adımları takip edebilirsiniz:

1. **Projeyi Klonlayın veya Klasöre Atın:**
   Proje dosyalarını XAMPP'ın içerisindeki `htdocs` klasörünün altına yükleyin. (Örn: `C:\xampp\htdocs\fitcheck\`)

2. **API Anahtarını Tanımlayın:**
   `db_config.php` dosyasını açın ve Groq Console üzerinden aldığınız API anahtarınızı ilgili değişkene tanımlayın:
   ```php
   $groq_api_key = "gsk_YOUR_GROQ_API_KEY";
