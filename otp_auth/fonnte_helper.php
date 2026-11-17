<?php
/**
 * =========================================================================
 * Helper Integrasi API WhatsApp Gateway Fonnte
 * Proyek: nnaco.sa Store
 * =========================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Memvalidasi dan menormalkan format nomor telepon lokal Indonesia ke format internasional Fonnte (628xxx)
 * 
 * Contoh konversi:
 * - 08986817584   -> 628986817584
 * - +628986817584 -> 628986817584
 * - 628986817584  -> 628986817584
 * - 8986817584    -> 628986817584
 * 
 * @param string|int $phone
 * @return string Nomor dalam format 628xxx
 * @throws InvalidArgumentException Jika format nomor tidak valid
 */
function formatPhoneNumber(string|int $phone): string {
    // 1. Bersihkan semua karakter selain angka
    $cleaned = preg_replace('/[^0-9]/', '', trim((string) $phone));

    if (empty($cleaned)) {
        throw new InvalidArgumentException("Nomor WhatsApp tidak boleh kosong.");
    }

    // 2. Konversi awalan nomor
    if (str_starts_with($cleaned, '0')) {
        // Ganti '0' di depan dengan '62' (misal: 0812 -> 62812)
        $cleaned = '62' . substr($cleaned, 1);
    } elseif (str_starts_with($cleaned, '8')) {
        // Jika dimulai langsung dengan '8', tambahkan '62'
        $cleaned = '62' . $cleaned;
    } elseif (!str_starts_with($cleaned, '62')) {
        throw new InvalidArgumentException("Format nomor tidak valid. Pastikan nomor diawali 08 atau 628.");
    }

    // 3. Validasi panjang nomor telepon Indonesia (umumnya 10 hingga 15 digit angka)
    $len = strlen($cleaned);
    if ($len < 10 || $len > 16) {
        throw new InvalidArgumentException("Panjang nomor telepon tidak valid ({$len} digit). Minimal 10 dan maksimal 15 digit.");
    }

    return $cleaned;
}

/**
 * Menyembunyikan sebagian digit nomor WhatsApp untuk privasi tampilan UI (Contoh: 0898****7584)
 * 
 * @param string $phone
 * @return string
 */
function maskPhoneNumber(string|int $phone): string {
    $phoneStr = (string) $phone;
    $len = strlen($phoneStr);
    if ($len <= 6) {
        return $phoneStr;
    }
    $start = substr($phoneStr, 0, 4);
    $end = substr($phoneStr, -4);
    return $start . '****' . $end;
}

/**
 * Mengirim pesan WhatsApp melalui Fonnte API via cURL
 * 
 * @param string $targetPhone Nomor telepon yang sudah diformat (628xxx)
 * @param string $message Teks pesan yang akan dikirim
 * @return array ['success' => bool, 'message' => string, 'raw' => array|null]
 */
function sendWhatsAppMessage(string $targetPhone, string $message): array {
    $curl = curl_init();

    $postData = [
        'target'      => $targetPhone,
        'message'     => $message,
        'countryCode' => '62'
    ];

    curl_setopt_array($curl, [
        CURLOPT_URL            => FONNTE_ENDPOINT,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 20,           // Batas waktu timeout 20 detik
        CURLOPT_CONNECTTIMEOUT => 10,           // Batas waktu koneksi awal 10 detik
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => $postData,
        CURLOPT_HTTPHEADER     => [
            'Authorization: ' . FONNTE_TOKEN
        ],
        CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,   // Cegah timeout IPv6 di lingkungan Windows
        CURLOPT_SSL_VERIFYPEER => false,               // Mencegah hang TLS cert revocation di Windows XAMPP
        CURLOPT_SSL_VERIFYHOST => 0
    ]);

    $response = curl_exec($curl);
    $curlErr  = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // 1. Tangani error level koneksi cURL / Jaringan
    if ($curlErr) {
        error_log("[Fonnte cURL Error] {$curlErr}");
        return [
            'success' => false,
            'message' => 'Gagal terhubung ke gateway WhatsApp: ' . $curlErr,
            'raw'     => null
        ];
    }

    // 2. Tangani respon non-200 dari server
    if ($httpCode !== 200) {
        error_log("[Fonnte HTTP Error] HTTP Code {$httpCode}: {$response}");
        return [
            'success' => false,
            'message' => "Gateway WhatsApp mengembalikan status HTTP {$httpCode}.",
            'raw'     => $response
        ];
    }

    // 3. Parse JSON response dari Fonnte
    $result = json_decode((string)$response, true);
    if (!is_array($result)) {
        error_log("[Fonnte JSON Parse Error] " . $response);
        return [
            'success' => false,
            'message' => 'Respon gateway WhatsApp tidak valid atau rusak.',
            'raw'     => $response
        ];
    }

    // 4. Periksa status pengiriman dari Fonnte API
    // Format sukses Fonnte: {"status": true, "id": [...], "target": [...], "message": "..."}
    if (isset($result['status']) && $result['status'] === true) {
        return [
            'success' => true,
            'message' => 'Pesan WhatsApp berhasil dikirim.',
            'raw'     => $result
        ];
    }

    // Fonnte mengembalikan status false (misal device disconnect, kuota habis, dll)
    $errorMessage = $result['reason'] ?? $result['message'] ?? 'Pengiriman pesan ditolak oleh WhatsApp gateway.';
    error_log("[Fonnte API Rejected] " . json_encode($result));

    return [
        'success' => false,
        'message' => $errorMessage,
        'raw'     => $result
    ];
}

/**
 * Template pesan ramah khusus pengiriman Kode OTP Login
 * 
 * @param string $targetPhone
 * @param string $otpCode
 * @param int $expirationMinutes
 * @return array
 */
function sendWhatsAppOTP(string $targetPhone, string $otpCode, int $expirationMinutes = OTP_EXPIRATION_MINUTES): array {
    $appName = STORE_NAME;
    
    // Template Pesan Ramah & Informatif
    $message = "Halo Pelanggan Setia *{$appName}*! 🌸\n\n"
             . "Kode verifikasi (OTP) untuk login ke akun Anda adalah:\n"
             . "*{$otpCode}*\n\n"
             . "⚠️ *PENTING:* Jangan berikan kode ini kepada siapa pun, termasuk pihak yang mengatasnamakan {$appName}.\n"
             . "Kode ini hanya berlaku selama *{$expirationMinutes} menit*.\n\n"
             . "Selamat berbelanja perhiasan & aksesoris favorit Anda! ✨";

    return sendWhatsAppMessage($targetPhone, $message);
}

/**
 * Mengirim notifikasi status pesanan terbaru ke nomor WhatsApp pelanggan
 * 
 * @param string|int $targetPhone Nomor WhatsApp tujuan
 * @param array $order Data pesanan (id, total_amount, shipping_address, username, dll)
 * @param string $newStatus Status baru pesanan (processing, packed, shipped, completed, cancelled)
 * @return array
 */
function sendWhatsAppOrderStatusUpdate(string|int $targetPhone, array $order, string $newStatus): array {
    $appName = STORE_NAME;
    $orderNumber = 'TRX-' . str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT);
    $customerName = htmlspecialchars($order['username'] ?? 'Pelanggan');
    $totalAmount = 'Rp ' . number_format((float)($order['total_amount'] ?? 0), 0, ',', '.');
    $address = trim($order['shipping_address'] ?? '-');
    $trackingUrl = STORE_URL . '/order/detail/' . $order['id'];

    $statusLabels = [
        'pending'    => '⏳ MENUNGGU KONFIRMASI',
        'processing' => '👩‍🍳 SEDANG DIPROSES',
        'packed'     => '📦 SIAP DIKEMAS',
        'shipped'    => '🚚 DALAM PENGIRIMAN',
        'completed'  => '✅ SELESAI & DITERIMA',
        'cancelled'  => '❌ DIBATALKAN'
    ];

    $statusDescriptions = [
        'pending'    => 'Pesanan Anda telah masuk ke sistem kami dan menunggu konfirmasi.',
        'processing' => 'Pesanan Anda sedang kami siapkan dan diproses dengan penuh cinta & ketelitian! ✨',
        'packed'     => 'Barang pesanan Anda sudah selesai dikemas rapi dan siap diserahkan ke jasa kurir.',
        'shipped'    => 'Paket Anda sudah diserahkan ke kurir dan sedang dalam perjalanan menuju alamat pengiriman.',
        'completed'  => 'Pesanan Anda telah sampai dan selesai! Terima kasih telah berbelanja di nnaco.sa. Jangan lupa beri ulasan terbaik Kakak ya! 💖',
        'cancelled'  => 'Pesanan Anda telah dibatalkan. Jika ada pertanyaan atau kendala, silakan hubungi customer service kami.'
    ];

    $statusText = $statusLabels[strtolower($newStatus)] ?? strtoupper($newStatus);
    $statusDesc = $statusDescriptions[strtolower($newStatus)] ?? 'Status pesanan Anda telah diperbarui.';

    $message = "Halo Kak *{$customerName}*! 🌸\n\n"
             . "Ada kabar terbaru mengenai pesanan Anda di *{$appName}*:\n\n"
             . "🧾 *No. Pesanan:* `{$orderNumber}`\n"
             . "📌 *Status Terbaru:* *{$statusText}*\n"
             . "💰 *Total Pembayaran:* {$totalAmount}\n"
             . "📍 *Alamat Pengiriman:* {$address}\n\n"
             . "📝 *Catatan:* {$statusDesc}\n\n"
             . "Kakak bisa memantau status pesanan kapan saja di sini:\n"
             . "👉 {$trackingUrl}\n\n"
             . "Terima kasih banyak telah mempercayai *{$appName}*! 🧸✨";

    try {
        $formatted = formatPhoneNumber($targetPhone);
        return sendWhatsAppMessage($formatted, $message);
    } catch (Exception $e) {
        error_log("[sendWhatsAppOrderStatusUpdate Error] " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

