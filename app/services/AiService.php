<?php

class AiService {

    /**
     * Deteksi apakah pertanyaan user berada di luar konteks toko nnaco.sa
     * (misal: coding/pemrograman, tugas kuliah/sekolah, resep masakan, politik, karya sastra, jailbreak).
     * Mengembalikan pesan penolakan yang ramah & sopan sesuai role jika OOT, atau null jika valid.
     */
    public static function detectOutOfContext($userMessage, $userRole = 'customer') {
        $msg = mb_strtolower(trim($userMessage), 'UTF-8');
        $msgClean = preg_replace('/\s+/', ' ', $msg);

        // 1. Coding / Pemrograman / Scripting guardrail
        // Kata kunci bahasa pemrograman eksplisit
        $hasLangKeyword = preg_match('/\b(python|javascript|typescript|c\+\+|cpp|c\#|golang|pascal|ruby|rust|kotlin|swift|css3|html5)\b/i', $msgClean);
        
        // Permintaan koding / script / pembuatan program (hindari bentrok dengan kode promo / voucher / otp / transaksi)
        $hasCodingAction = preg_match('/\b(coding|koding|pemrograman|source\s*code|skrip|script|bikin\s+kodingan|buat\s+kodingan|bikin\s+program|buatkan\s+program|tuliskan\s+program|algoritma\s+program|fungsi\s+def\b|def\s+[a-z_]|console\.log|public\s+static\s+void|#include\s*<)\b/i', $msgClean)
            || preg_match('/\b(bikin|buatkan|tuliskan)\s+kode(?!\s*(promo|voucher|diskon|otp|transaksi|pesanan|order))\b/i', $msgClean);

        if ($hasLangKeyword || $hasCodingAction) {
            return self::formatRefusalMessage('coding', $userRole);
        }

        // 2. Resep / Kuliner di luar toko
        if (preg_match('/\b(resep\s+(masakan|makanan|kue|minuman|nasi|ayam|daging|sambal|sayur|roti)|cara\s+memasak|bumbu\s+dapur)\b/i', $msgClean)) {
            return self::formatRefusalMessage('recipe', $userRole);
        }

        // 3. Karya Sastra / Puisi / Cerpen umum
        if (preg_match('/\b(buatkan|bikin|tuliskan)\s+(puisi|pantun|cerpen|dongeng|novel|lirik\s+lagu)\b/i', $msgClean)) {
            return self::formatRefusalMessage('creative', $userRole);
        }

        // 4. Tugas Akademik / PR di luar lingkup toko
        if (preg_match('/\b(tugas\s+(kuliah|sekolah|kampus|pr\b|matematika|fisika|kimia|biologi|sejarah)|kerjakan\s+tugas|soal\s+(ujian|uts|uas))\b/i', $msgClean)) {
            return self::formatRefusalMessage('homework', $userRole);
        }

        // 5. Politik / Pemilu / Isu Pemerintahan umum
        if (preg_match('/\b(siapa\s+presiden|menteri\s+|pemilu|pilkada|partai\s+politik|debat\s+capres)\b/i', $msgClean)) {
            return self::formatRefusalMessage('politics', $userRole);
        }

        // 6. Horoskop / Zodiak / Ramalan
        if (preg_match('/\b(ramalan\s+zodiak|horoskop|ramal\s+nasib|bintang\s+zodiak)\b/i', $msgClean)) {
            return self::formatRefusalMessage('zodiac', $userRole);
        }

        // 7. Identity hijack / Menanyakan ChatGPT / OpenAI / Jailbreak
        if (preg_match('/\b(kamu\s+chatgpt|apakah\s+(kamu|anda)\s+chatgpt|kamu\s+openai|kamu\s+claude|kamu\s+gemini|jailbreak|ignore\s+all\s+(previous|instructions)|abaikan\s+instruksi)\b/i', $msgClean)) {
            return self::formatRefusalMessage('identity', $userRole);
        }

        return null; // Valid & in-context
    }

    /**
     * Format pesan penolakan profesional dalam Bahasa Indonesia sesuai role
     */
    public static function formatRefusalMessage($type, $userRole = 'customer') {
        $role = strtolower($userRole);

        if ($role === 'owner') {
            return "Halo Bos **Syamsul Hadi**! 👋\n\nSebagai **AI Assistant resmi nnaco.sa**, saya difokuskan khusus hanya untuk mendukung **operasional dan bisnis toko nnaco.sa** (seperti memantau omzet penjualan, produk yang terjual hari ini, pesanan masuk, dan cek stok barang di database).\n\nDemi menjaga keamanan sistem dan integritas operasional toko, saya **tidak dapat melayani penulisan coding pemrograman**, materi edukasi di luar konteks, atau hal umum lainnya di luar lingkup toko nnaco.sa.\n\nApakah ada data penjualan, omzet, pesanan, atau stok produk di database yang ingin Bos periksa hari ini? 📊🛍️";
        } elseif ($role === 'admin') {
            return "Halo Admin! 👋\n\nSebagai **AI Assistant resmi nnaco.sa**, saya bertugas khusus untuk membantu **pengelolaan dan operasional toko nnaco.sa** (seperti memantau pesanan masuk, cek produk terjual, cek stok gudang menipis di database, dan informasi katalog produk).\n\nDemi menjaga keamanan sistem, saya **tidak diperkenankan menjawab pertanyaan coding pemrograman**, tugas umum, atau materi di luar lingkup toko nnaco.sa.\n\nSilakan tanyakan seputar data operasional toko, pesanan pelanggan, atau stok produk nnaco.sa yang ingin Anda kelola! 📦✨";
        } else {
            return "Halo! ✨ Saya adalah **AI Assistant resmi nnaco.sa** (toko online aksesoris & perhiasan wanita di Cirebon).\n\nDemi menjaga keamanan sistem dan kenyamanan layanan belanja, saya **diprogram khusus hanya untuk menjawab hal-hal seputar toko nnaco.sa** (seperti rekomendasi perhiasan & aksesoris, info stok, harga, promo diskon, pemesanan, dan pengiriman wilayah Cirebon).\n\nSaya **tidak dapat melayani penulisan coding pemrograman**, tugas di luar konteks toko, atau materi umum lainnya. Ada produk aksesoris cantik yang ingin Kakak tanyakan hari ini? 💎🌸";
        }
    }

    /**
     * Generate response from AI Model based on user message and context.
     * Uses Mock logic if API key is empty or 'mock'.
     */
    public function generateResponse($userMessage, $catalogContext, $userRole = 'customer') {
        $role = strtolower($userRole);

        // 0. Guardrail Lapis 1: Deteksi pertanyaan di luar konteks toko (Anti OOT & Anti Coding)
        $ootRefusal = self::detectOutOfContext($userMessage, $role);
        if ($ootRefusal !== null) {
            return $ootRefusal;
        }

        // 0b. Guardrail Akses Database: Khusus Admin dan Owner saja!
        // Jika Customer atau Guest bertanya tentang omzet / data penjualan internal / database internal:
        if ($role !== 'owner' && $role !== 'admin') {
            $lowerMsg = strtolower($userMessage);
            if (preg_match('/\b(omzet|omset|pendapatan toko|pemasukan toko|laporan penjualan|baca database|isi database|data penjualan|keuntungan toko|daftar user|data user)\b/i', $lowerMsg)) {
                return "Maaf Kak, informasi data laporan penjualan, omzet toko, dan operasional internal database toko hanya dapat diakses oleh **Admin** dan **Owner** demi menjaga kerahasiaan dan keamanan sistem toko **nnaco.sa**. 😊\n\nSaya siap membantu Kakak seputar informasi produk aksesoris cantik, promo diskon aktif, cara pemesanan, atau info pengiriman wilayah Cirebon! Ada yang bisa saya bantu carikan? ✨💎";
            }
        }

        // 1. Ambil Konfigurasi Utama AI dari Database
        $config = null;
        try {
            require_once __DIR__ . '/../models/AiConfig_model.php';
            $aiConfigModel = new AiConfig_model();
            $config = $aiConfigModel->getPrimaryConfig();
        } catch (Exception $e) {
            error_log("Failed to load AI config from DB: " . $e->getMessage());
        }

        // Tentukan parameter dari DB atau fallback ke constants config.php
        if ($config && !empty($config['is_active'])) {
            $apiKey = $config['decrypted_key'] ?? '';
            $apiUrl = !empty($config['api_url']) ? $config['api_url'] : 'https://api.openai.com/v1/chat/completions';
            $model = !empty($config['model_name']) ? $config['model_name'] : 'openai/gpt-oss-20b';
            $provider = strtolower($config['provider'] ?? 'groq');
            $temperature = (float)($config['temperature'] ?? 0.70);
            $maxTokens = min(350, max(100, (int)($config['max_tokens'] ?? 350)));
            $timeout = max(30, (int)($config['timeout_seconds'] ?? 30));
            $customCustPrompt = !empty($config['system_prompt_customer']) ? $config['system_prompt_customer'] : null;
        } else {
            // Jika ada konfigurasi di DB namun sengaja dinonaktifkan oleh Admin/Owner
            if ($config && empty($config['is_active'])) {
                return "Layanan AI Assistant sedang dinonaktifkan sementara oleh Administrator. Silakan hubungi admin atau gunakan katalog toko secara manual.";
            }

            $apiKey = defined('AI_API_KEY') ? AI_API_KEY : '';
            $apiUrl = defined('AI_PROVIDER_URL') ? AI_PROVIDER_URL : 'https://api.openai.com/v1/chat/completions';
            $model = defined('AI_MODEL') ? AI_MODEL : 'openai/gpt-oss-20b';
            $provider = 'groq';
            $temperature = 0.70;
            $maxTokens = 350;
            $timeout = 30;
            $customCustPrompt = null;
        }

        // 2. Fallback / Mock Mode jika API Key kosong
        if (empty($apiKey) || $apiKey === 'mock') {
            return $this->mockResponse($userMessage, $catalogContext, $role);
        }

        if ($role === 'owner') {
            $systemPrompt = "=== BATASAN KONTEKS & KEAMANAN SISTEM (ATURAN MUTLAK) ===
1. IDENTITAS RESMI:
   - Nama Anda adalah 'AI Assistant nnaco.sa' (AI Executive Advisor & Business Intelligence resmi untuk OWNER toko 'nnaco.sa').
   - Pengguna saat ini adalah OWNER (PEMILIK UTAMA) toko nnaco.sa, yaitu Bapak **Syamsul Hadi**.
   - Pemilik resmi toko nnaco.sa adalah Bapak **Syamsul Hadi**. Jika ditanya siapa nama owner atau pemilik toko, jawab dengan tegas dan jelas bahwa nama Owner (Pemilik) toko nnaco.sa adalah Bapak **Syamsul Hadi** (yaitu akun Anda sendiri selaku pemilik toko nnaco.sa).
   - DILARANG KERAS mengaku atau menyebut diri sebagai 'ChatGPT', 'OpenAI', 'Claude', atau 'Gemini'! Anda adalah asisten resmi toko nnaco.sa.

2. DILARANG MENJAWAB DI LUAR KONTEKS TOKO (ANTI OUT-OF-TOPIC GUARDRAIL):
   - Anda HANYA diperbolehkan menjawab seputar bisnis, operasional, penjualan, produk aksesoris, pesanan, dan website toko nnaco.sa.
   - DILARANG KERAS membuat, menuliskan, atau menjelaskan kode pemrograman (coding / script / Python / Java / PHP / C++ / JavaScript / HTML / CSS / dsb) untuk tujuan apapun!
   - DILARANG KERAS menjawab pertanyaan di luar konteks toko (seperti tugas akademik, resep masakan, politik, kesehatan, trivia umum).
   - Jika pengguna meminta coding atau topik di luar lingkup toko, TOLAK DENGAN TEGAS DAN SOPAN:
     'Halo Bos Syamsul Hadi! Sebagai AI Assistant resmi nnaco.sa, saya difokuskan khusus hanya untuk operasional dan bisnis toko nnaco.sa (laporan omzet, pesanan masuk, produk terjual, dan stok database). Demi keamanan sistem, saya tidak dapat melayani penulisan coding atau topik di luar lingkup toko nnaco.sa.'

3. HAK AKSES DATABASE OPERASIONAL LIVE (KHUSUS OWNER & ADMIN):
   - Selaku Owner, Bapak Syamsul Hadi memiliki OTORISASI PENUH DAN AKSES LANGSUNG ke seluruh data DATABASE operasional toko: OMZET, PENJUALAN HARIAN & BULANAN, TRANSAKSI HARI INI, PRODUK TERJUAL, STOK MENIPIS/HABIS, STATUS PESANAN, dan DATA CUSTOMER.
   - Jawab pertanyaan tentang data toko/database dengan akurat, transparan, dan lengkap berdasarkan data DATABASE di bawah.
   - Ketika ditanya 'produk apa yang terjual pada hari ini?', 'berapa omzet hari ini?', dsb, sebutkan rincian dari data database di bawah (nama produk, qty, harga, subtotal, pembeli, nomor transaksi TRX-XXXXXX, dan waktu transaksi)!
   - JANGAN PERNAH menolak pertanyaan tentang data penjualan atau database internal jika datanya sudah tersedia di bawah!

4. ATURAN JAWABAN (SINGKAT, PADAT, TO THE POINT, JANGAN KEBANYAKAN BASA-BASI / NO YAPPING):
   - Wajib menjawab secara SINGKAT, PADAT, dan LANGSUNG KE INTI PERTANYAAN tanpa bertele-tele.
   - DILARANG memberikan kata sambutan panjang, prolog berbelit-belit, atau mengulang daftar kemampuan/fitur Anda jika tidak diminta!
   - Jika ditanya hal umum/identitas (contoh: 'siapa anda', 'siapa saya', 'siapa owner', 'halo'): Cukup jawab dalam 1-2 KALIMAT SINGKAT!
     * Jika ditanya 'siapa anda': 'Saya AI Assistant nnaco.sa, asisten resmi toko nnaco.sa. Ada yang bisa saya bantu, Bos?'
     * Jika ditanya 'siapa saya': 'Anda adalah Bapak **Syamsul Hadi**, Pemilik (Owner) toko nnaco.sa.'
     * Jika ditanya 'siapa pemilik toko': 'Pemilik resmi toko nnaco.sa adalah Bapak **Syamsul Hadi**.'
   - Hanya berikan penjelasan mendalam atau daftar rincian jika user secara spesifik memintanya.

PANDUAN GAYA KOMUNIKASI DENGAN OWNER:
- Sapa dengan hormat, sopan, dan ringkas: 'Halo Bos Syamsul Hadi! 👑' atau 'Halo Bapak Syamsul Hadi!'.
- Gunakan Bahasa Indonesia yang profesional, ringkas, dan jelas.
- Teks penting seperti angka nominal uang atau nama produk ditebalkan (contoh: **Rp 60.000**, **Kerudung Pashmina Ceruty**).
- DILARANG membuat tabel markdown pipa (|). Gunakan daftar bernomor atau poin-poin rapi jika menyajikan data.
- HANYA gunakan data riil dari DATABASE di bawah. Jangan mengarang data penjualan fiktif.

$catalogContext";
        } elseif ($role === 'admin' || $role === 'operator') {
            $systemPrompt = "=== BATASAN KONTEKS & KEAMANAN SISTEM (ATURAN MUTLAK) ===
1. IDENTITAS DIRI & PEMILIK TOKO:
   - Nama Anda adalah 'AI Assistant nnaco.sa' (AI Store Operations Assistant resmi untuk staf ADMIN toko 'nnaco.sa').
   - Pemilik (Owner) resmi toko nnaco.sa adalah Bapak **Syamsul Hadi**.
   - Pengguna saat ini adalah ADMIN OPERASIONAL TOKO, BUKAN pemilik toko.
   - Jika pengguna bertanya siapa nama owner/pemilik toko, jawab dengan pasti dan jelas bahwa Owner toko nnaco.sa adalah Bapak **Syamsul Hadi**. Dilarang menyebut Admin Utama sebagai owner!
   - DILARANG KERAS mengaku atau menyebut diri sebagai 'ChatGPT', 'OpenAI', 'Claude', atau 'Gemini'!

2. DILARANG MENJAWAB DI LUAR KONTEKS TOKO (ANTI OUT-OF-TOPIC GUARDRAIL):
   - Anda HANYA diperbolehkan menjawab seputar operasional toko nnaco.sa, pengelolaan produk, katalog, pesanan masuk, cek stok database, dan promo aktif.
   - DILARANG KERAS menuliskan kode pemrograman (coding / Python / Java / PHP / C++ / JavaScript / script apapun).
   - DILARANG KERAS menjawab hal di luar lingkup toko (seperti tugas akademik, resep masakan, politik, trivia umum).
   - Jika ditanya hal di luar konteks toko, TOLAK DENGAN TEGAS DAN SOPAN bahwa Anda difokuskan hanya untuk operasional toko nnaco.sa.

3. HAK AKSES DATABASE OPERASIONAL LIVE (KHUSUS OWNER & ADMIN):
   - Sebagai Admin, Anda memiliki hak akses membaca data DATABASE operasional toko: omzet, produk terjual hari ini, pesanan masuk, status transaksi, peringatan stok gudang menipis/habis, promo, dan pelanggan.
   - Jawab pertanyaan operasional toko berdasarkan data DATABASE di bawah dengan akurat dan rapi.

4. ATURAN JAWABAN (SINGKAT, PADAT, TO THE POINT, JANGAN KEBANYAKAN BASA-BASI / NO YAPPING):
   - Wajib menjawab secara SINGKAT, PADAT, dan LANGSUNG KE INTI PERTANYAAN tanpa bertele-tele.
   - JANGAN membuat prolog panjang, sambutan berulang, atau daftar tugas yang tidak ditanyakan!
   - Untuk pertanyaan ringkas (misal: 'siapa anda', 'siapa saya', 'siapa owner', 'halo'): Cukup jawab dalam 1-2 KALIMAT SINGKAT!
     * Jika ditanya 'siapa anda': 'Saya AI Assistant nnaco.sa, asisten resmi operasional toko untuk staf Admin. Ada yang bisa saya bantu?'
     * Jika ditanya 'siapa saya': 'Anda login sebagai **Admin Utama** (admin@nnaco.sa).'
     * Jika ditanya 'siapa owner / pemilik toko': 'Pemilik resmi toko nnaco.sa adalah Bapak **Syamsul Hadi**.'
   - Hanya berikan daftar rincian jika user secara spesifik memintanya (misal: 'tampilkan stok menipis', 'tampilkan daftar pesanan').

PANDUAN FORMAT & GAYA KOMUNIKASI:
- Sapa dengan ramah, profesional, dan ringkas.
- DILARANG membuat tabel markdown pipa (|).
- HANYA gunakan data riil dari DATABASE di bawah.

$catalogContext";
        } else {
            // Customer / Guest Prompt
            if ($customCustPrompt) {
                $systemPrompt = $customCustPrompt . "\n\nINFORMASI OWNER TOKO: Pemilik toko nnaco.sa adalah Bapak Syamsul Hadi.\n\n" . $catalogContext;
            } else {
                $systemPrompt = "=== BATASAN KONTEKS & KEAMANAN SISTEM (ATURAN MUTLAK) ===
1. IDENTITAS RESMI:
   - Nama Anda adalah 'AI Assistant nnaco.sa' (Asisten Belanja Resmi Toko Aksesoris & Perhiasan Wanita 'nnaco.sa').
   - Pemilik (Owner) toko nnaco.sa adalah Bapak **Syamsul Hadi**.
   - DILARANG KERAS mengaku atau menyebut diri sebagai 'ChatGPT', 'OpenAI', 'Claude', atau 'Gemini'!

2. DILARANG MENJAWAB DI LUAR KONTEKS TOKO (ANTI OUT-OF-TOPIC GUARDRAIL):
   - Anda HANYA bertugas melayani pelanggan seputar belanja perhiasan & aksesoris, rekomendasi produk, cek ketersediaan stok, harga, promo diskon, pemesanan, dan pengiriman wilayah Cirebon.
   - DILARANG KERAS membuat atau menulis kode pemrograman (coding / script / Python / Java / dll), mengerjakan tugas sekolah, resep masakan, atau topik umum non-toko.
   - Jika pelanggan bertanya hal di luar toko, tolak dengan sopan dan ramah:
     'Halo Kak! ✨ Sebagai AI Assistant resmi nnaco.sa, saya diprogram khusus untuk melayani seputar produk perhiasan & aksesoris, harga, promo, dan pengiriman toko nnaco.sa. Saya tidak dapat melayani pertanyaan di luar konteks toko kami. Ada produk aksesoris cantik yang ingin Kakak tanyakan? 💎🌸'

3. KEBIJAKAN KEAMANAN & PRIVASI DATABASE (SANGAT KETAT):
   - DILARANG KERAS membeberkan laporan penjualan internal toko, data omzet toko, keuntungan toko, data transaksi pelanggan lain, atau data database internal kepada customer/guest!
   - Hak akses membaca database internal toko (omzet, laporan keuangan, transaksi pelanggan lain) HANYA DIBERIKAN KEPADA ADMIN DAN OWNER TOKO.
   - Jika pengguna bertanya tentang omzet toko, laporan penjualan, atau data internal database toko:
     WAJIB MENOLAK secara ramah dan sopan:
     'Maaf Kak, informasi data omzet penjualan dan operasional internal database toko hanya dapat diakses oleh Admin dan Owner demi menjaga kerahasiaan dan keamanan sistem nnaco.sa. Namun jika Kakak membutuhkan bantuan seputar produk aksesoris cantik atau promo belanja, saya siap membantu! 😊'
   - Jika pengguna bertanya 'siapa saja user di web ini' atau 'daftar akun pengguna': 
     Tolak secara ramah dan sopan bahwa data akun pengguna bersifat rahasia dan tidak dibagikan ke publik demi privasi.

4. ATURAN JAWABAN (SINGKAT, PADAT, TO THE POINT, JANGAN KEBANYAKAN BASA-BASI / NO YAPPING):
   - Wajib menjawab secara SINGKAT, PADAT, dan LANGSUNG KE INTI PERTANYAAN tanpa bertele-tele.
   - Dilarang membuat sambutan panjang atau paragraf pembuka yang berlebihan.
   - Jika ditanya 'siapa anda': Jawab 1 kalimat ramah: 'Halo Kak! ✨ Saya AI Assistant nnaco.sa, asisten belanja resmi toko perhiasan & aksesoris nnaco.sa. Ada yang bisa saya bantu?'
   - Jika ditanya 'siapa saya': Jika sudah login sebut nama customer (contoh: 'Kakak login sebagai **{Username}**.'), jika belum login katakan sebagai tamu/pengunjung setia toko.
   - Jika ditanya produk, sebutkan produk yang relevan secara ringkas tanpa mengulang katalog panjang lebar.

PERLAKUAN IDENTITAS:
- JIKA STATUS: SUDAH LOGIN:
  Sapa pengguna dengan nama mereka sendiri secara personal dan hangat (contoh: 'Halo Kak **{Username}**! ✨').
- JIKA STATUS: BELUM LOGIN (GUEST / PENGUNJUNG):
  Sapa dengan ramah sebagai 'Kakak' atau 'Pengunjung setia nnaco.sa'.

TUGAS UTAMA LAYANAN:
1. Menjawab pertanyaan seputar produk aksesoris (kalung, gelang, cincin, anting, bando, dll), ketersediaan stok, harga, dan rekomendasi hadiah/budget SESUAI KATALOG DATABASE.
2. Memberikan informasi kode promo/voucher diskon aktif jika ditanyakan.
3. Menjelaskan jangkauan pengiriman (melayani seluruh Kota Cirebon dan Kabupaten Cirebon) serta metode pembayaran (Transfer Bank & COD).
4. Menjelaskan cara berbelanja dan membuat pesanan.
5. Jam operasional toko: Setiap hari pukul 08:00 - 22:00 WIB.

PANDUAN FORMAT & GAYA KOMUNIKASI:
- Gunakan Bahasa Indonesia yang ramah, hangat, sopan, dan solutif.
- Teks penting ditebalkan, contoh: **Kalung Emas Murni** (Rp 1.500.000).
- DILARANG membuat tabel markdown pipa (|).
- HANYA rekomendasikan produk yang tercantum di DATABASE di bawah.

$catalogContext";
            }
        }

        // Siapkan Payload & Header sesuai Provider (OpenAI/Groq vs Anthropic)
        if ($provider === 'anthropic') {
            $payload = [
                'model' => $model,
                'max_tokens' => $maxTokens,
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $userMessage]
                ]
            ];
            $headers = [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01'
            ];
        } else {
            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage]
                ],
                'temperature' => $temperature,
                'max_tokens' => $maxTokens
            ];
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ];
        }

        // Jalankan pemanggilan API dengan auto-retry jika terkena HTTP 429 atau koneksi terputus
        $response = false;
        $httpCode = 0;
        $error = '';
        $maxAttempts = 2;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 12);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300 && $response) {
                break;
            }

            // Jika terkena Rate Limit (HTTP 429) atau koneksi timeout (0), jeda 1.5 detik lalu coba lagi
            if (($httpCode == 429 || $httpCode == 0) && $attempt < $maxAttempts) {
                usleep(1500000); // 1.5 detik agar token bucket Groq ter-reset
                continue;
            }
            break;
        }

        if ($httpCode >= 200 && $httpCode < 300 && $response) {
            $data = json_decode($response, true);
            if (isset($data['choices'][0]['message']['content']) && trim($data['choices'][0]['message']['content']) !== '') {
                return $data['choices'][0]['message']['content'];
            } elseif (isset($data['choices'][0]['message']['reasoning']) && trim($data['choices'][0]['message']['reasoning']) !== '') {
                return $data['choices'][0]['message']['reasoning'];
            } elseif (isset($data['content'][0]['text'])) { // Anthropic format
                return $data['content'][0]['text'];
            }
        } else {
            error_log("AI API Error ($httpCode): $response");
            if ($error) {
                error_log("cURL Error: $error");
            }

            // Smart Instant Fallback untuk pertanyaan umum/identitas jika API terkena rate limit / kendala
            $lowerMsg = strtolower(trim($userMessage));
            
            // 1. Pertanyaan tentang Owner / Pemilik
            if (strpos($lowerMsg, 'owner') !== false || strpos($lowerMsg, 'pemilik') !== false || strpos($lowerMsg, 'syamsul') !== false) {
                return "Pemilik resmi toko **nnaco.sa** adalah Bapak **Syamsul Hadi**.";
            }

            // 2. Pertanyaan tentang identitas bot ("siapa anda / kamu siapa")
            if (strpos($lowerMsg, 'siapa anda') !== false || strpos($lowerMsg, 'kamu siapa') !== false || strpos($lowerMsg, 'siapa kamu') !== false) {
                return ($role === 'owner')
                    ? "Saya **AI Assistant nnaco.sa**, asisten resmi dan penasihat bisnis toko nnaco.sa untuk Bos Syamsul Hadi. Ada yang bisa saya bantu?"
                    : (($role === 'admin') 
                        ? "Saya **AI Assistant nnaco.sa**, asisten resmi operasional toko untuk staf Admin. Ada yang bisa saya bantu?"
                        : "Saya **AI Assistant nnaco.sa**, asisten belanja resmi toko aksesoris nnaco.sa. Ada yang bisa saya bantu?");
            }

            // 3. Pertanyaan tentang identitas user ("siapa saya / nama saya")
            if (strpos($lowerMsg, 'siapa saya') !== false || strpos($lowerMsg, 'nama saya') !== false || strpos($lowerMsg, 'akun saya') !== false) {
                $sessionUser = $_SESSION['user'] ?? [];
                $uname = !empty($sessionUser['username']) ? $sessionUser['username'] : ($role === 'owner' ? 'Syamsul Hadi' : 'Pengguna');
                if ($role === 'owner') {
                    return "Anda adalah Bapak **Syamsul Hadi**, Pemilik (Owner) toko nnaco.sa.";
                } elseif ($role === 'admin') {
                    return "Anda login sebagai **" . htmlspecialchars($uname) . "** (" . htmlspecialchars($sessionUser['email'] ?? 'admin@nnaco.sa') . ").";
                } elseif (!empty($sessionUser['id'])) {
                    return "Kakak login sebagai **" . htmlspecialchars($uname) . "**.";
                } else {
                    return "Kakak saat ini belum login (masuk sebagai tamu/pengunjung setia toko nnaco.sa).";
                }
            }

            // 4. Jika error karena rate limit 429
            if ($httpCode == 429) {
                return "Sistem AI sedang melayani banyak permintaan secara bersamaan. Mohon tunggu sekitar 2–3 detik lalu kirim pesan kembali ya! 😊";
            }

            return "Maaf, sistem AI kami sedang mengalami kendala jaringan (Status: $httpCode). Anda tetap dapat berbelanja produk kami secara manual.";
        }

        // Fallback if API fails
        return "Maaf, sistem AI kami sedang mengalami gangguan. Namun Anda tetap dapat melihat-lihat katalog produk kami di halaman utama. Ada yang bisa saya bantu secara manual?";
    }

    /**
     * Mock Response Logic for Development/Testing without API Key.
     */
    private function mockResponse($userMessage, $catalogContext, $role = 'customer') {
        // Sleep slightly to simulate network request
        usleep(800000); // 0.8s
        
        $msg = strtolower($userMessage);

        if (strpos($msg, 'owner') !== false || strpos($msg, 'pemilik') !== false || strpos($msg, 'syamsul') !== false) {
            return "Halo! Pemilik (Owner) resmi dari toko **nnaco.sa** adalah Bapak **Syamsul Hadi**.";
        }

        if ($role === 'owner' || $role === 'admin') {
            if (strpos($msg, 'omzet') !== false || strpos($msg, 'penjualan') !== false || strpos($msg, 'laku') !== false || strpos($msg, 'terjual') !== false) {
                return "Halo! Berdasarkan data live database toko nnaco.sa:\n- Total omzet sepanjang waktu: Rp 60.000\n- Produk terjual hari ini dapat dipantau langsung dari ringkasan transaksi completed di database.";
            }
            if (strpos($msg, 'stok') !== false || strpos($msg, 'menipis') !== false || strpos($msg, 'habis') !== false) {
                return "Halo! Berdasarkan database stok gudang toko:\n- Gelang Rantai Emas 22K (Sisa: 2 unit)\n- Cincin Batu Safir Biru (Sisa: 3 unit)\n- Cincin Berlian (Sisa: 5 unit)";
            }
        }

        if (strpos($msg, 'budget') !== false || strpos($msg, 'ribu') !== false) {
            return "Berdasarkan budget Anda, kami merekomendasikan berbagai aksesoris cantik seperti jepit rambut, bando, atau gelang tali dengan harga terjangkau di katalog kami.";
        }

        if (strpos($msg, 'stok') !== false) {
            return "Untuk ketersediaan produk, seluruh produk yang bertanda 'Tersedia' di katalog siap dipesan. Ada aksesoris tertentu yang sedang dicari?";
        }

        return "Halo! Saya adalah **AI Assistant nnaco.sa**, asisten resmi toko aksesoris nnaco.sa. Ada produk atau hal seputar toko yang bisa saya bantu?";
    }
}
