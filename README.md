# nnaco.sa

## Deskripsi Aplikasi
**nnaco.sa** adalah aplikasi web e-commerce komprehensif yang dirancang untuk toko perhiasan (kalung, gelang, cincin, anting). Aplikasi ini menyediakan pengalaman berbelanja yang responsif dengan dukungan multi-role (Customer, Admin, Operator, Owner) serta integrasi AI untuk membantu layanan pelanggan dan analisis bisnis.

## Fitur
1. **Multi-Role Authentication & Authorization**:
   - **Customer**: Menjelajah produk, menambahkan ke keranjang, checkout, dan memberikan ulasan.
   - **Admin**: Mengelola data produk, kategori, dan pelanggan.
   - **Operator**: Mengelola pesanan (Order Management), mengubah status pengiriman.
   - **Owner**: Melihat dashboard analitik komprehensif, tren penjualan, dan AI Business Insight.
2. **AI Chatbot Assistant**: Asisten AI yang memahami katalog produk secara *real-time* untuk membantu customer.
3. **AI Business Insight**: Generator analisis bisnis otomatis untuk Owner berdasarkan tren penjualan.
4. **Keranjang & Checkout**: Proses belanja yang mulus dan tervalidasi stoknya.
5. **Keamanan**: Proteksi CSRF, password hashing, dan pengamanan rute.

## Teknologi
- **Frontend**: HTML5, CSS3, Vanilla JavaScript, Bootstrap 5, Chart.js.
- **Backend**: PHP (Native, Custom MVC Architecture).
- **Database**: MySQL (menggunakan PDO Prepared Statements).
- **AI Integration**: OpenAI API (atau provider yang kompatibel).

## Struktur Folder
```text
nnaco.sa/
├── app/
│   ├── controllers/   # Logika bisnis dan pengatur alur data
│   ├── models/        # Interaksi dengan database (CRUD)
│   ├── services/      # Service khusus seperti AiService dan BusinessInsightService
│   └── views/         # Tampilan UI HTML & PHP (Bootstrap 5)
├── config/            # Konfigurasi konstanta (Database, AI, URL)
├── core/              # Inti framework MVC (App, Controller, Database, AuthMiddleware)
├── database/          # File skema SQL dan Seeder (Data Dummy)
├── public/            # Document root (index.php, CSS, JS, Images)
└── README.md          # Dokumentasi proyek
```

## Instalasi
1. Clone repositori ini atau ekstrak file sumber ke dalam folder server lokal Anda (misal: `htdocs` di XAMPP).
2. Pastikan nama folder proyek adalah `nnaco.sa`.

## Konfigurasi Database
1. Buka phpMyAdmin (atau klien MySQL lainnya) dan buat database baru bernama `nnaco_sa`.
2. Impor file `database/schema.sql` untuk membuat tabel-tabel yang diperlukan.
3. Impor file `database/seeder.sql` untuk memasukkan data awal dan akun demo.
4. Buka `config/config.php` dan pastikan kredensial database sudah benar:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'nnaco_sa');
   ```

## Konfigurasi Environment & AI
Aplikasi ini menggunakan API Key untuk fitur Chatbot dan Insight. Anda dapat mengaturnya di level environment OS/Server (Apache/Nginx) atau menggunakan fungsi bawaan PHP.
1. Pastikan variabel lingkungan `AI_API_KEY` tersedia di server Anda. 
2. Jika Anda menggunakan API provider selain OpenAI, atur juga `AI_PROVIDER_URL`.
3. Alternatif, jika Anda tidak dapat mengatur environment variable, Anda dapat mengaturnya di dalam konfigurasi server lokal (misalnya di VirtualHost) atau secara berhati-hati melalui file khusus yang tidak diunggah ke repositori.

## Cara Menjalankan Aplikasi
1. Jalankan Apache dan MySQL melalui XAMPP/MAMP/LAMP.
2. Buka browser dan akses alamat URL: `http://localhost/nnaco.sa/public`
3. Gunakan akun demo di bawah ini untuk menguji fungsionalitas berbagai *role*.

## Akun Demo
Berikut adalah kredensial untuk mengakses masing-masing role (Password default untuk semua akun demo: `password123`):

| Role | Email | Password |
|---|---|---|
| **Owner** | owner@nnaco.sa | password123 |
| **Admin** | admin@nnaco.sa | password123 |
| **Operator** | operator@nnaco.sa | password123 |
| **Customer** | budi@gmail.com | password123 |
