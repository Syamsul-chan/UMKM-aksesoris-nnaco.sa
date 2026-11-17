<?php

class BusinessInsightService {
    
    public function analyze($metrics, $periodLabel) {
        // Ambil konfigurasi utama dari database jika ada
        $config = null;
        try {
            require_once __DIR__ . '/../models/AiConfig_model.php';
            $aiConfigModel = new AiConfig_model();
            $config = $aiConfigModel->getPrimaryConfig();
        } catch (Exception $e) {
            error_log("Failed to load AI config in BusinessInsight: " . $e->getMessage());
        }

        if ($config && !empty($config['is_active'])) {
            $apiKey = $config['decrypted_key'] ?? '';
            $apiUrl = !empty($config['api_url']) ? $config['api_url'] : 'https://api.openai.com/v1/chat/completions';
            $model = !empty($config['model_name']) ? $config['model_name'] : 'openai/gpt-oss-20b';
            $temperature = (float)($config['temperature'] ?? 0.60);
            $maxTokens = (int)($config['max_tokens'] ?? 400);
            $timeout = (int)($config['timeout_seconds'] ?? 30);
            $customInsightPrompt = !empty($config['system_prompt_insight']) ? $config['system_prompt_insight'] : null;
        } else {
            $apiKey = defined('AI_API_KEY') ? AI_API_KEY : '';
            $apiUrl = defined('AI_PROVIDER_URL') ? AI_PROVIDER_URL : 'https://api.openai.com/v1/chat/completions';
            $model = defined('AI_MODEL') ? AI_MODEL : 'openai/gpt-oss-20b';
            $temperature = 0.60;
            $maxTokens = 400;
            $timeout = 30;
            $customInsightPrompt = null;
        }

        // Format data into text
        $dataText = $this->formatMetricsToText($metrics, $periodLabel);

        if (empty($apiKey) || $apiKey === 'mock') {
            return $this->mockResponse($dataText, $metrics);
        }

        if ($customInsightPrompt) {
            $systemPrompt = $customInsightPrompt;
        } else {
            $systemPrompt = "Anda adalah Business Analyst AI untuk toko aksesoris 'nnaco_sa'.
Tugas Anda adalah menganalisis data ringkasan bisnis yang diberikan dan memberikan Insight Bisnis yang sangat ringkas, padat, dan profesional.
Insight harus berisi:
- Kinerja penjualan (berdasarkan Total Revenue & Orders)
- Tren produk terlaris
- Peringatan stok (jika ada produk yang stoknya menipis)
- Rekomendasi bisnis singkat yang masuk akal

JANGAN MENYEBUTKAN DATA YANG TIDAK ADA DI DALAM TEKS RINGKASAN DATA. Jawab dalam format markdown yang mudah dibaca (gunakan bullet points atau bold jika perlu). Maksimal 4-5 kalimat/poin pendek.";
        }

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => "Berikut adalah Ringkasan Data:\n" . $dataText]
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, max(25, (int)$timeout));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['choices'][0]['message']['content'])) {
                return $data['choices'][0]['message']['content'];
            }
        }

        return "Maaf, sistem AI Insight gagal memproses data saat ini.";
    }

    private function formatMetricsToText($metrics, $periodLabel) {
        $text = "Periode Analisis: $periodLabel\n";
        $text .= "Total Pendapatan (Revenue): Rp" . number_format($metrics['stats']['total_revenue'] ?? 0, 0, ',', '.') . "\n";
        $text .= "Total Pesanan Sukses: " . ($metrics['stats']['total_orders'] ?? 0) . "\n";
        $text .= "Customer Baru: " . ($metrics['stats']['total_customers'] ?? 0) . "\n\n";

        $text .= "Produk Terlaris:\n";
        if (!empty($metrics['topProducts'])) {
            foreach($metrics['topProducts'] as $tp) {
                $text .= "- {$tp['name']} (Terjual: {$tp['total_sold']} unit)\n";
            }
        } else {
            $text .= "- Belum ada produk terjual.\n";
        }

        $text .= "\nPeringatan Stok Menipis:\n";
        if (!empty($metrics['lowStock'])) {
            foreach($metrics['lowStock'] as $ls) {
                $text .= "- {$ls['name']} (Sisa: {$ls['stock']} unit)\n";
            }
        } else {
            $text .= "- Stok semua produk aman.\n";
        }

        return $text;
    }

    private function mockResponse($dataText, $metrics) {
        usleep(1000000); // Simulate network latency

        $revenue = $metrics['stats']['total_revenue'] ?? 0;
        $topProductName = !empty($metrics['topProducts']) ? $metrics['topProducts'][0]['name'] : 'Belum ada produk';
        $lowStockCount = count($metrics['lowStock'] ?? []);

        $insight = "*(Mock AI Insight - API Key Not Configured)*\n\n";
        $insight .= "**1. Kinerja Penjualan:**\n";
        $insight .= "Selama periode ini, toko menghasilkan Rp" . number_format($revenue, 0, ',', '.') . " yang menunjukkan aktivitas transaksi " . ($revenue > 0 ? "berjalan baik." : "sedang sepi.") . "\n\n";
        
        $insight .= "**2. Analisis Produk:**\n";
        $insight .= "Produk **$topProductName** memimpin penjualan. Disarankan untuk mempromosikan produk ini lebih agresif di halaman utama.\n\n";
        
        $insight .= "**3. Peringatan Inventaris:**\n";
        if ($lowStockCount > 0) {
            $insight .= "Terdapat $lowStockCount produk yang stoknya menipis (≤ 5 unit). Segera lakukan *restock* agar tidak kehilangan potensi penjualan.";
        } else {
            $insight .= "Tidak ada produk kritis yang membutuhkan *restock* mendesak saat ini.";
        }

        return $insight;
    }
}
