<?php

require_once __DIR__ . '/../services/AiService.php';

class ChatbotController extends Controller {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function sendMessage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');

        if (empty($message)) {
            echo json_encode(['status' => 'error', 'message' => 'Pesan tidak boleh kosong']);
            return;
        }

        try {
            $db = new Database();

            // 0. Ambil Data Resmi Pemilik (Owner) Toko dari Database
            $db->query("SELECT u.id, u.username, u.email, u.phone_number 
                        FROM users u 
                        JOIN roles r ON u.role_id = r.id 
                        WHERE LOWER(r.name) = 'owner' 
                        ORDER BY u.id ASC LIMIT 1");
            $ownerRow = $db->single();
            $ownerName = !empty($ownerRow['username']) ? $ownerRow['username'] : 'Syamsul Hadi';
            $ownerEmail = !empty($ownerRow['email']) ? $ownerRow['email'] : 'owner@nnaco.sa';

            // 1. Fetch Current User Identity & Profile from DB / Session
            $isLoggedIn = isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
            $currentUserText = "=== STATUS PENGGUNA YANG SEDANG CHAT ===\n";
            if ($isLoggedIn) {
                $userId = $_SESSION['user']['id'];
                $db->query("SELECT u.id, u.username, u.email, r.name as role_name, u.created_at 
                            FROM users u 
                            LEFT JOIN roles r ON u.role_id = r.id 
                            WHERE u.id = :id");
                $db->bind(':id', $userId);
                $userData = $db->single();

                if (!empty($userData['username'])) {
                    $_SESSION['user']['username'] = $userData['username'];
                }

                $db->query("SELECT address_line, city, postal_code FROM addresses WHERE user_id = :id LIMIT 1");
                $db->bind(':id', $userId);
                $userAddress = $db->single();

                $db->query("SELECT COUNT(*) as order_count, COALESCE(SUM(total_amount), 0) as total_spent FROM orders WHERE user_id = :id");
                $db->bind(':id', $userId);
                $userOrders = $db->single();

                $cartCount = !empty($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
                $activeRole = strtolower($userData['role_name'] ?? $_SESSION['user']['role'] ?? 'customer');

                $currentUserText .= "- Status: SUDAH LOGIN\n";
                $currentUserText .= "- ID Pengguna: " . ($userData['id'] ?? $userId) . "\n";
                $currentUserText .= "- Nama / Username: " . ($userData['username'] ?? $_SESSION['user']['username'] ?? 'User') . "\n";
                $currentUserText .= "- Email: " . ($userData['email'] ?? $_SESSION['user']['email'] ?? '-') . "\n";
                $currentUserText .= "- Peran / Role: " . ($userData['role_name'] ?? $_SESSION['user']['role'] ?? 'Customer') . "\n";
                if ($activeRole === 'owner') {
                    $currentUserText .= "- IDENTITAS KHUSUS: PENGGUNA INI ADALAH OWNER / PEMILIK TOKO NNACO.SA, YAITU BAPAK " . strtoupper($ownerName) . ".\n";
                } elseif ($activeRole === 'admin') {
                    $currentUserText .= "- IDENTITAS KHUSUS: PENGGUNA INI ADALAH ADMIN OPERASIONAL TOKO (STAF), BUKAN OWNER. OWNER TOKO ADALAH BAPAK " . strtoupper($ownerName) . ".\n";
                }
                $currentUserText .= "- Tanggal Bergabung: " . ($userData['created_at'] ?? '2026-08-29') . "\n";
                if ($userAddress) {
                    $currentUserText .= "- Alamat Terdaftar: {$userAddress['address_line']}, {$userAddress['city']} ({$userAddress['postal_code']})\n";
                } else {
                    $currentUserText .= "- Alamat Terdaftar: Belum ada alamat tersimpan\n";
                }
                $currentUserText .= "- Riwayat Pesanan: " . ($userOrders['order_count'] ?? 0) . " pesanan (Total Belanja: Rp " . number_format($userOrders['total_spent'] ?? 0, 0, ',', '.') . ")\n";
                $currentUserText .= "- Barang di Keranjang Belanja: " . $cartCount . " item\n\n";
            } else {
                $currentUserText .= "- Status: BELUM LOGIN (GUEST / PENGUNJUNG)\n";
                $currentUserText .= "- Keterangan: Pengguna saat ini sedang berselancar sebagai Tamu / Guest tanpa akun login aktif.\n\n";
            }

            $userRole = strtolower($userData['role_name'] ?? $_SESSION['user']['role'] ?? 'guest');
            $isOwner = ($userRole === 'owner');
            $isAdmin = ($userRole === 'admin');
            
            // KHUSUS ADMIN DAN OWNER SAJA YANG BERHAK MEMBACA DATABASE OPERASIONAL LIVE!
            $canReadDatabase = ($isOwner || $isAdmin);

            // 2. Jika Pengguna adalah OWNER atau ADMIN: Muat Data Laporan Penjualan, Stok, & Bisnis Live dari DB
            $salesContextText = "";
            if ($canReadDatabase) {
                // Total Omzet Keseluruhan & Bulan Ini & Hari Ini (Completed Orders)
                $db->query("SELECT COALESCE(SUM(total_amount), 0) as total_rev, COUNT(id) as total_trx FROM orders WHERE status = 'completed'");
                $allTimeSales = $db->single();

                $startMonth = date('Y-m-01 00:00:00');
                $endMonth = date('Y-m-t 23:59:59');
                $db->query("SELECT COALESCE(SUM(total_amount), 0) as month_rev, COUNT(id) as month_trx FROM orders WHERE status = 'completed' AND created_at >= :sm AND created_at <= :em");
                $db->bind(':sm', $startMonth);
                $db->bind(':em', $endMonth);
                $thisMonthSales = $db->single();

                $today = date('Y-m-d');
                $startToday = $today . ' 00:00:00';
                $endToday = $today . ' 23:59:59';
                $db->query("SELECT COALESCE(SUM(total_amount), 0) as today_rev, COUNT(id) as today_trx FROM orders WHERE status = 'completed' AND created_at >= :st AND created_at <= :et");
                $db->bind(':st', $startToday);
                $db->bind(':et', $endToday);
                $todaySales = $db->single();

                // Detail Produk yang Terjual Hari Ini (Completed)
                $db->query("SELECT od.order_id, od.quantity, od.price, (od.quantity * od.price) as subtotal, p.name as product_name, u.username as customer_name, o.created_at
                            FROM order_details od
                            JOIN orders o ON od.order_id = o.id
                            JOIN products p ON od.product_id = p.id
                            LEFT JOIN users u ON o.user_id = u.id
                            WHERE o.status = 'completed' AND o.created_at >= :st AND o.created_at <= :et
                            ORDER BY o.created_at DESC");
                $db->bind(':st', $startToday);
                $db->bind(':et', $endToday);
                $todaySoldItems = $db->resultSet();

                // Semua Pesanan Masuk Hari Ini (Semua Status)
                $db->query("SELECT o.id, o.total_amount, o.status, o.created_at, u.username as customer_name
                            FROM orders o
                            LEFT JOIN users u ON o.user_id = u.id
                            WHERE o.created_at >= :st AND o.created_at <= :et
                            ORDER BY o.id DESC");
                $db->bind(':st', $startToday);
                $db->bind(':et', $endToday);
                $todayOrders = $db->resultSet();

                // Riwayat Transaksi Sukses Terbaru (Completed) - 15 Transaksi
                $db->query("SELECT o.id, o.total_amount, o.status, o.created_at, u.username as customer_name
                            FROM orders o
                            LEFT JOIN users u ON o.user_id = u.id
                            WHERE o.status = 'completed'
                            ORDER BY o.created_at DESC
                            LIMIT 15");
                $recentCompletedOrders = $db->resultSet();

                // Produk Terlaris (Top Selling)
                $db->query("SELECT p.name, SUM(od.quantity) as total_sold, SUM(od.quantity * od.price) as total_revenue
                            FROM order_details od
                            JOIN orders o ON od.order_id = o.id
                            JOIN products p ON od.product_id = p.id
                            WHERE o.status = 'completed'
                            GROUP BY p.id
                            ORDER BY total_sold DESC
                            LIMIT 5");
                $topProducts = $db->resultSet();

                // Ringkasan Status Pesanan Toko
                $db->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
                $orderStatusRows = $db->resultSet();
                $statusSummary = [];
                foreach ($orderStatusRows as $os) {
                    $statusSummary[strtolower($os['status'])] = $os['count'];
                }

                // Laporan Peringatan Stok Gudang (Stok Menipis <= 5 dan Stok Habis = 0)
                $db->query("SELECT p.name, c.name as category_name, p.stock, p.price 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            WHERE p.is_active = 1 AND p.stock <= 5 
                            ORDER BY p.stock ASC, p.name ASC");
                $lowStockProducts = $db->resultSet();

                // Ringkasan Data Customer / Pengguna Terdaftar
                $db->query("SELECT COUNT(u.id) as total_customers 
                            FROM users u 
                            JOIN roles r ON u.role_id = r.id 
                            WHERE LOWER(r.name) = 'customer'");
                $custCountRow = $db->single();
                $totalRegisteredCustomers = $custCountRow['total_customers'] ?? 0;

                $salesContextText .= "=== DATA RESMI LAPORAN PENJUALAN & OPERASIONAL DATABASE NNACO.SA (LIVE DATABASE) ===\n";
                $salesContextText .= "Otorisasi Akses: Diberikan secara eksklusif kepada " . strtoupper($userRole) . " toko untuk analisis dan monitoring operasional.\n\n";

                $salesContextText .= "1. RINGKASAN OMZET & PENDAPATAN RESMI (Status: Completed):\n";
                $salesContextText .= "- Hari Ini (" . date('d M Y') . "): Rp " . number_format($todaySales['today_rev'], 0, ',', '.') . " (" . $todaySales['today_trx'] . " transaksi sukses)\n";
                $salesContextText .= "- Bulan Ini (" . date('F Y') . "): Rp " . number_format($thisMonthSales['month_rev'], 0, ',', '.') . " (" . $thisMonthSales['month_trx'] . " transaksi sukses)\n";
                $salesContextText .= "- Total Sepanjang Waktu: Rp " . number_format($allTimeSales['total_rev'], 0, ',', '.') . " (" . $allTimeSales['total_trx'] . " transaksi sukses)\n\n";

                $salesContextText .= "2. RINCIAN PRODUK YANG TERJUAL HARI INI (" . date('d M Y') . "):\n";
                if (!empty($todaySoldItems)) {
                    foreach ($todaySoldItems as $tsi) {
                        $trxCode = 'TRX-' . str_pad($tsi['order_id'], 6, '0', STR_PAD_LEFT);
                        $salesContextText .= "- Produk: **{$tsi['product_name']}** | Terjual: {$tsi['quantity']} pcs | Harga: Rp " . number_format($tsi['price'], 0, ',', '.') . " | Subtotal: Rp " . number_format($tsi['subtotal'], 0, ',', '.') . " | Pembeli: {$tsi['customer_name']} | No. Transaksi: {$trxCode} (Waktu: " . date('H:i', strtotime($tsi['created_at'])) . " WIB)\n";
                    }
                } else {
                    $salesContextText .= "- Belum ada produk yang terjual berstatus 'completed' hari ini.\n";
                }
                $salesContextText .= "\n";

                $salesContextText .= "3. DAFTAR SEMUA PESANAN MASUK HARI INI (" . date('d M Y') . "):\n";
                if (!empty($todayOrders)) {
                    foreach ($todayOrders as $to) {
                        $trxCode = 'TRX-' . str_pad($to['id'], 6, '0', STR_PAD_LEFT);
                        $salesContextText .= "- [{$trxCode}] Status: " . strtoupper($to['status']) . " | Total Tagihan: Rp " . number_format($to['total_amount'], 0, ',', '.') . " | Customer: {$to['customer_name']} | Pukul: " . date('H:i', strtotime($to['created_at'])) . " WIB\n";
                    }
                } else {
                    $salesContextText .= "- Tidak ada pesanan masuk pada hari ini.\n";
                }
                $salesContextText .= "\n";

                $salesContextText .= "4. DAFTAR TRANSAKSI SELESAI (COMPLETED) TERBARU:\n";
                if (!empty($recentCompletedOrders)) {
                    foreach ($recentCompletedOrders as $rco) {
                        $trxCode = 'TRX-' . str_pad($rco['id'], 6, '0', STR_PAD_LEFT);
                        $db->query("SELECT p.name, od.quantity, od.price FROM order_details od JOIN products p ON od.product_id = p.id WHERE od.order_id = :oid");
                        $db->bind(':oid', $rco['id']);
                        $itemsInOrder = $db->resultSet();
                        $itemDesc = [];
                        foreach ($itemsInOrder as $iio) {
                            $itemDesc[] = "{$iio['name']} ({$iio['quantity']}x)";
                        }
                        $salesContextText .= "- [{$trxCode}] Tanggal: " . date('d M Y, H:i', strtotime($rco['created_at'])) . " | Pembeli: {$rco['customer_name']} | Item: " . implode(', ', $itemDesc) . " | Total: Rp " . number_format($rco['total_amount'], 0, ',', '.') . "\n";
                    }
                } else {
                    $salesContextText .= "- Belum ada data transaksi selesai.\n";
                }
                $salesContextText .= "\n";

                $salesContextText .= "5. PRODUK TERLARIS (TOP SELLING):\n";
                if (!empty($topProducts)) {
                    foreach ($topProducts as $idx => $tp) {
                        $salesContextText .= "- #" . ($idx + 1) . " {$tp['name']} (Terjual: {$tp['total_sold']} pcs, Total Pendapatan: Rp " . number_format($tp['total_revenue'], 0, ',', '.') . ")\n";
                    }
                }
                $salesContextText .= "\n";

                $salesContextText .= "6. STATUS KESELURUHAN PESANAN TOKO:\n";
                $salesContextText .= "- Pending (Menunggu Diproses): " . ($statusSummary['pending'] ?? 0) . "\n";
                $salesContextText .= "- Processing (Sedang Diproses): " . ($statusSummary['processing'] ?? 0) . "\n";
                $salesContextText .= "- Shipped (Sedang Dikirim): " . ($statusSummary['shipped'] ?? 0) . "\n";
                $salesContextText .= "- Completed (Selesai/Lunas): " . ($statusSummary['completed'] ?? 0) . "\n";
                $salesContextText .= "- Cancelled (Dibatalkan): " . ($statusSummary['cancelled'] ?? 0) . "\n\n";

                $salesContextText .= "7. LAPORAN STOK GUDANG & PERINGATAN STOK MENIPIS (LIVE DATABASE):\n";
                if (!empty($lowStockProducts)) {
                    foreach ($lowStockProducts as $lsp) {
                        if ($lsp['stock'] == 0) {
                            $salesContextText .= "- ❌ [STOK HABIS (0 unit)] {$lsp['name']} (Kategori: {$lsp['category_name']}, Harga: Rp " . number_format($lsp['price'], 0, ',', '.') . ")\n";
                        } else {
                            $salesContextText .= "- ⚠️ [STOK MENIPIS (Sisa: {$lsp['stock']} unit)] {$lsp['name']} (Kategori: {$lsp['category_name']}, Harga: Rp " . number_format($lsp['price'], 0, ',', '.') . ")\n";
                        }
                    }
                } else {
                    $salesContextText .= "- Seluruh stok produk dalam kondisi aman (> 5 unit).\n";
                }
                $salesContextText .= "\n";

                $salesContextText .= "8. DATA PELANGGAN / CUSTOMER TOKO (LIVE DATABASE):\n";
                $salesContextText .= "- Total Customer Terdaftar di Sistem: {$totalRegisteredCustomers} Pengguna Aktif\n\n";
            }

            // 3. Fetch All Active Products with Category & Description
            $db->query("SELECT p.id, p.name, c.name as category_name, p.price, p.discount_percent, p.promo_price, p.is_promo, p.stock, p.description 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.is_active = 1 
                        ORDER BY c.name ASC, p.price ASC");
            $products = $db->resultSet();

            // 4. Fetch Active Promo Codes
            $db->query("SELECT name, code, discount_type, discount_value, end_date 
                        FROM promos 
                        WHERE is_active = 1 
                        ORDER BY id ASC");
            $promos = $db->resultSet();

            // 5. Fetch Category Names
            $db->query("SELECT name FROM categories ORDER BY name ASC");
            $categories = $db->resultSet();

            // 6. Build Live Context
            $contextText = $currentUserText;
            
            // Sertakan context data operasional database HANYA jika Admin atau Owner
            if (!empty($salesContextText)) {
                $contextText .= $salesContextText;
            }

            $contextText .= "=== INFO TOKO & STRUKTUR PEMILIK RESMI ===\n";
            $contextText .= "- Nama Toko: nnaco_sa (Aksesoris & Fashion Jewelry)\n";
            $contextText .= "- PEMILIK / OWNER TOKO RESMI: Bapak " . strtoupper($ownerName) . " ({$ownerName})\n";
            $contextText .= "- Email Owner: {$ownerEmail}\n";
            $contextText .= "- PENTING: Jika ada yang bertanya 'siapa nama owner', 'siapa pemilik toko', atau sejenisnya, jawab bahwa Owner toko nnaco_sa adalah Bapak {$ownerName}. JANGAN PERNAH keliru menyebut Admin Utama sebagai owner!\n";
            $contextText .= "- Jam Operasional: Setiap Hari, 08:00 - 22:00 WIB\n";
            $contextText .= "- Pembayaran: Transfer Bank (BCA/Mandiri/BRI) & COD (Bayar di Tempat)\n";
            $contextText .= "- Wilayah Pengiriman: Seluruh Kota & Kabupaten Cirebon.\n\n";

            // Promo Context
            $contextText .= "=== PROMO & VOUCHER DISKON AKTIF ===\n";
            if (!empty($promos)) {
                foreach ($promos as $promo) {
                    $val = ($promo['discount_type'] === 'percentage') 
                        ? (floatval($promo['discount_value']) . "%") 
                        : ("Rp " . number_format($promo['discount_value'], 0, ',', '.'));
                    $contextText .= "- Kode Voucher: [{$promo['code']}] - {$promo['name']} (Diskon $val)\n";
                }
            } else {
                $contextText .= "- Saat ini belum ada promo aktif.\n";
            }
            $contextText .= "\n";

            // Category Summary
            $catNames = array_map(function($c) { return $c['name']; }, $categories);
            $contextText .= "=== KATEGORI PRODUK TERSEDIA ===\n";
            $contextText .= implode(", ", $catNames) . "\n\n";

            // Catalog List
            $contextText .= "=== KATALOG PRODUK DARI DATABASE (Total " . count($products) . " Produk) ===\n";
            foreach ($products as $p) {
                if ($canReadDatabase) {
                    $stockStatus = ($p['stock'] > 0) ? "Stok: {$p['stock']} unit" : "HABIS (0 unit)";
                    $promoTag = (!empty($p['is_promo']) && !empty($p['discount_percent']) && $p['discount_percent'] > 0) ? " [PROMO DISKON {$p['discount_percent']}% -> Rp " . number_format($p['promo_price'], 0, ',', '.') . "]" : "";
                    $desc = !empty($p['description']) ? " | Info: " . mb_substr(strip_tags($p['description']), 0, 45) : "";
                    $contextText .= "- [ID:{$p['id']}] {$p['name']} ({$p['category_name']}) | Rp " . number_format($p['price'], 0, ',', '.') . "$promoTag | $stockStatus$desc\n";
                } else {
                    $stockStatus = ($p['stock'] > 0) ? "Tersedia" : "Stok Habis";
                    $promoTag = (!empty($p['is_promo']) && !empty($p['discount_percent']) && $p['discount_percent'] > 0) ? " [PROMO DISKON {$p['discount_percent']}% -> Rp " . number_format($p['promo_price'], 0, ',', '.') . "]" : "";
                    $desc = !empty($p['description']) ? " | Info: " . mb_substr(strip_tags($p['description']), 0, 45) : "";
                    $contextText .= "- {$p['name']} ({$p['category_name']}) | Rp " . number_format($p['price'], 0, ',', '.') . "$promoTag | $stockStatus$desc\n";
                }
            }

            // 7. Call AI Service
            $aiService = new AiService();
            $reply = $aiService->generateResponse($message, $contextText, $userRole);

            echo json_encode([
                'status' => 'success',
                'reply' => $reply
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal membaca database: ' . $e->getMessage()
            ]);
        }
    }
}
