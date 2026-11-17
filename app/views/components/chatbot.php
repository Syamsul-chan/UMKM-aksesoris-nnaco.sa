<?php
    $cbRole = strtolower($_SESSION['user']['role'] ?? 'guest');
    $cbIsOwner = ($cbRole === 'owner');
    $cbIsAdmin = ($cbRole === 'admin');
    $cbCanReadDatabase = ($cbIsOwner || $cbIsAdmin);
    $cbUsername = $_SESSION['user']['username'] ?? 'Kakak';
    if ($cbIsOwner && ($cbUsername === 'Owner Nnaco' || empty($cbUsername))) {
        $cbUsername = 'Syamsul Hadi';
    }
    $cbPlaceholder = $cbIsOwner 
        ? "Tanya omzet, produk terjual, laporan penjualan, stok database..." 
        : ($cbIsAdmin 
            ? "Tanya pesanan masuk, produk terjual, stok gudang database, promo..." 
            : "Tanya produk aksesoris, harga, ketersediaan, promo diskon...");
?>
<!-- Chatbot Floating Button -->
<div id="chatbotWrapper">
    <button class="chatbot-floating-btn" id="chatbotBtn" title="Buka AI Assistant nnaco_sa" aria-label="Buka AI Chatbot">
        <div class="chatbot-btn-inner">
            <i class="bi bi-robot fs-4"></i>
            <span class="chatbot-btn-badge">AI</span>
            <span class="chatbot-pulse-ring"></span>
        </div>
    </button>
</div>

<!-- Chatbot Window -->
<div class="chatbot-window d-none" id="chatbotWindow" role="dialog" aria-labelledby="chatbotTitle">
    
    <!-- Header -->
    <div class="chatbot-header">
        <div class="d-flex align-items-center gap-2">
            <div class="chatbot-avatar">
                <i class="bi bi-robot text-white"></i>
                <span class="chatbot-status-dot" title="Online"></span>
            </div>
            <div>
                <h6 class="mb-0 fw-bold text-white" id="chatbotTitle" style="font-size: 14.5px; letter-spacing: 0.2px;">AI Assistant nnaco.sa</h6>
                <div class="d-flex align-items-center gap-1">
                    <span class="status-indicator-ping"></span>
                    <small class="chatbot-subtitle"><?= $cbCanReadDatabase ? 'Akses Database Toko • Online' : 'Asisten Belanja • Online' ?></small>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-1">
            <button type="button" class="chatbot-header-btn" id="clearChatbot" title="Bersihkan chat">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
            <button type="button" class="chatbot-header-btn" id="closeChatbot" title="Tutup chat">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <!-- Chat Messages Body -->
    <div class="chatbot-body" id="chatbotMessages">
        
        <!-- Security & Context Notice -->
        <div class="text-center my-1">
            <span class="chatbot-badge-notice">
                <?php if ($cbCanReadDatabase): ?>
                    <i class="bi bi-shield-check text-success me-1"></i> Terhubung langsung ke Database Operasional nnaco.sa
                <?php else: ?>
                    <i class="bi bi-shield-check text-primary me-1"></i> Asisten Belanja Resmi Toko nnaco.sa Aksesoris
                <?php endif; ?>
            </span>
        </div>

        <!-- Welcome Message from Bot -->
        <div class="chat-row bot-row">
            <div class="chat-avatar-mini">
                <i class="bi bi-robot"></i>
            </div>
            <div class="chat-bubble bot-bubble">
                <?php if ($cbIsOwner): ?>
                    <p class="mb-2">Halo Bos <strong><?= htmlspecialchars($cbUsername) ?></strong> (Owner Toko)! 👑📊</p>
                    <p class="mb-0">Saya <strong>AI Executive Assistant nnaco.sa</strong>. Saya siap membantu Bapak <strong>Syamsul Hadi</strong> memantau <strong>laporan penjualan</strong>, <strong>omzet harian & bulanan</strong>, <strong>produk yang terjual hari ini</strong>, serta status pesanan toko langsung dari database. Ada data bisnis yang ingin Bos periksa hari ini?</p>
                <?php elseif ($cbIsAdmin): ?>
                    <p class="mb-2">Halo Admin <strong><?= htmlspecialchars($cbUsername) ?></strong>! 💼</p>
                    <p class="mb-0">Saya <strong>AI Assistant nnaco.sa</strong>. Saya siap membantu memantau <strong>pesanan masuk</strong>, <strong>produk terjual hari ini</strong>, informasi owner (Bapak <strong>Syamsul Hadi</strong>), promo aktif, dan cek ketersediaan stok gudang di database.</p>
                <?php else: ?>
                    <p class="mb-2">Halo! ✨ Saya <strong>AI Assistant nnaco.sa</strong>.</p>
                    <p class="mb-0">Saya siap membantu Kakak mencari <strong>produk aksesoris cantik</strong>, info <strong>promo diskon</strong>, rekomendasi hadiah, dan informasi <strong>pengiriman wilayah Cirebon</strong>. Ada produk aksesoris yang ingin Kakak tanyakan hari ini? 💎🌸</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Suggestion Chips -->
        <div class="chatbot-suggestions" id="chatbotSuggestions">
            <div class="small text-muted fw-semibold mb-2" style="font-size: 11px;">PILIH PERTANYAAN CEPAT:</div>
            <div class="d-flex flex-wrap gap-2">
                <?php if ($cbIsOwner): ?>
                    <button type="button" class="suggestion-chip" data-query="produk apa yang terjual pada hari ini?">
                        🛍️ Produk Terjual Hari Ini
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Berapa total pemasukan dan omzet penjualan pada hari ini?">
                        💰 Pemasukan Hari Ini
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Bagaimana ringkasan laporan penjualan dan omzet bulan ini?">
                        📈 Penjualan Bulan Ini
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Apa saja daftar pesanan yang masuk pada hari ini beserta statusnya?">
                        📦 Pesanan Masuk Hari Ini
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Produk apa yang paling laris (top selling) di toko?">
                        🏆 Produk Terlaris
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Apakah ada produk yang stoknya menipis atau habis?">
                        ⚠️ Cek Stok Menipis
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Siapa nama pemilik toko ini dan apa peran saya?">
                        👑 Identitas Owner
                    </button>
                <?php elseif ($cbIsAdmin): ?>
                    <button type="button" class="suggestion-chip" data-query="Siapa nama owner toko nnaco_sa?">
                        👑 Siapa Nama Owner?
                    </button>
                    <button type="button" class="suggestion-chip" data-query="produk apa yang terjual pada hari ini?">
                        🛍️ Produk Terjual Hari Ini
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Berapa total pemasukan dan omzet penjualan pada hari ini?">
                        💰 Pemasukan Hari Ini
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Berapa total pesanan baru yang masuk hari ini?">
                        📦 Pesanan Masuk
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Apa saja promo diskon atau voucher yang sedang aktif?">
                        🏷️ Promo Aktif
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Produk apa saja yang stoknya menipis di database?">
                        ⚠️ Cek Stok Menipis
                    </button>
                <?php else: ?>
                    <button type="button" class="suggestion-chip" data-query="Siapa saya dan apa status akun saya saat ini?">
                        👤 Siapa Saya?
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Rekomendasi kalung emas dan kalung liontin yang ready">
                        💎 Kalung Cantik
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Ada cincin dan gelang apa saja yang tersedia beserta harganya?">
                        💍 Cincin & Gelang
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Ada kode promo diskon atau voucher apa saja hari ini?">
                        🎁 Kode Promo Aktif
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Apakah bisa kirim ke Harjamukti / seluruh daerah Kota dan Kab Cirebon?">
                        🚚 Pengiriman Cirebon
                    </button>
                    <button type="button" class="suggestion-chip" data-query="Rekomendasi aksesoris lucu dengan budget di bawah Rp 50.000">
                        💰 Budget Hemat
                    </button>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Typing / Thinking Skeleton Indicator -->
    <div class="chatbot-typing-area d-none" id="typingIndicator">
        <div class="chat-row bot-row mb-0">
            <div class="chat-avatar-mini">
                <i class="bi bi-robot"></i>
            </div>
            <div class="typing-bubble" style="display: flex; flex-direction: column; gap: 5px; padding: 9px 13px; min-width: 130px;">
                <div class="d-flex align-items-center gap-1 mb-1">
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                    <small class="text-muted ms-1" style="font-size: 10px; font-weight: 600;">AI Berpikir...</small>
                </div>
                <div class="skeleton-line" style="height: 7px; width: 110px; margin-bottom: 0;"></div>
                <div class="skeleton-line" style="height: 7px; width: 70px; margin-bottom: 0;"></div>
            </div>
        </div>
    </div>

    <!-- Footer / Input Form -->
    <div class="chatbot-footer">
        <form id="chatbotForm" class="d-flex align-items-center gap-2 m-0">
            <input type="text" class="chatbot-input" id="chatbotInput" placeholder="<?= htmlspecialchars($cbPlaceholder) ?>" autocomplete="off">
            <button type="submit" class="chatbot-send-btn" id="sendChatbotBtn" title="Kirim pesan">
                <i class="bi bi-send-fill text-white"></i>
            </button>
        </form>
    </div>
</div>

<style>
/* ==============================================================
   PREMIUM AI CHATBOT STYLING (MATCHES GRADIENT DASHBOARD THEME)
   ============================================================== */
#chatbotWrapper {
    position: fixed;
    bottom: 25px;
    right: 25px;
    z-index: 9999;
}

.chatbot-floating-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.2);
    background: #0f172a;
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.35), 0 3px 8px rgba(0, 0, 0, 0.12);
    cursor: pointer;
    outline: none;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
}

.chatbot-floating-btn:hover {
    transform: scale(1.08) translateY(-3px);
    background: #1e293b;
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.45), 0 6px 14px rgba(0, 0, 0, 0.15);
}

.chatbot-btn-inner {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
}

.chatbot-btn-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #10B981;
    color: #ffffff;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 10px;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.4);
    letter-spacing: 0.5px;
}

.chatbot-pulse-ring {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 2px solid rgba(224, 86, 253, 0.6);
    animation: chatPulseRing 2.5s infinite;
    pointer-events: none;
}

@keyframes chatPulseRing {
    0% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.22); opacity: 0; }
    100% { transform: scale(1.22); opacity: 0; }
}

/* Chatbot Main Window */
.chatbot-window {
    position: fixed;
    bottom: 98px;
    right: 25px;
    width: 390px;
    max-width: calc(100vw - 30px);
    height: 560px;
    max-height: calc(100vh - 120px);
    background: #F8FAFC;
    border-radius: 22px;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.22), 0 4px 18px rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 9999;
    border: 1px solid rgba(226, 232, 240, 0.95);
    animation: chatSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes chatSlideUp {
    from {
        opacity: 0;
        transform: translateY(25px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Chatbot Header */
.chatbot-header {
    background: linear-gradient(135deg, #634AE2 0%, #7C3AED 50%, #E056FD 100%);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 15px rgba(99, 74, 226, 0.25);
    flex-shrink: 0;
}

.chatbot-avatar {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.35);
}

.chatbot-status-dot {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 10px;
    height: 10px;
    background: #10B981;
    border: 2px solid #634AE2;
    border-radius: 50%;
}

.chatbot-subtitle {
    font-size: 11.5px;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
}

.status-indicator-ping {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10B981;
    display: inline-block;
}

.chatbot-header-btn {
    background: rgba(255, 255, 255, 0.15);
    border: none;
    color: #ffffff;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s;
}

.chatbot-header-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    color: #ffffff;
    transform: scale(1.05);
}

/* Chatbot Body */
.chatbot-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    background: #F8FAFC;
    scroll-behavior: smooth;
}

.chatbot-body::-webkit-scrollbar {
    width: 6px;
}

.chatbot-body::-webkit-scrollbar-track {
    background: transparent;
}

.chatbot-body::-webkit-scrollbar-thumb {
    background: #CBD5E1;
    border-radius: 10px;
}

.chatbot-body::-webkit-scrollbar-thumb:hover {
    background: #94A3B8;
}

/* Badge Notice */
.chatbot-badge-notice {
    display: inline-block;
    background: #EDF2F7;
    color: #4A5568;
    font-size: 10.5px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
    border: 1px solid #E2E8F0;
}

/* Chat Rows & Bubbles */
.chat-row {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    width: 100%;
}

.bot-row {
    justify-content: flex-start;
}

.user-row {
    justify-content: flex-end;
}

.chat-avatar-mini {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: linear-gradient(135deg, #634AE2, #855CE8);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    margin-top: 2px;
    box-shadow: 0 2px 6px rgba(99, 74, 226, 0.3);
}

/* HIGH CONTRAST BOT BUBBLE (FIXED!) */
.bot-bubble {
    background-color: #FFFFFF !important;
    color: #1E293B !important;
    border: 1px solid #E2E8F0 !important;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
    border-radius: 18px 18px 18px 4px !important;
    padding: 12px 15px;
    max-width: 86%;
    font-size: 13.5px;
    line-height: 1.55;
    word-break: break-word;
}

.bot-bubble strong {
    color: #432BB3;
    font-weight: 700;
}

.bot-bubble p:last-child {
    margin-bottom: 0;
}

/* HIGH CONTRAST USER BUBBLE (FIXED!) */
.user-bubble {
    background: linear-gradient(135deg, #634AE2 0%, #7C3AED 100%) !important;
    color: #FFFFFF !important;
    border: none !important;
    box-shadow: 0 4px 14px rgba(99, 74, 226, 0.3);
    border-radius: 18px 18px 4px 18px !important;
    padding: 10px 14px;
    max-width: 82%;
    font-size: 13.5px;
    line-height: 1.5;
    word-break: break-word;
}

/* Suggestion Chips */
.chatbot-suggestions {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    padding: 12px;
    margin-top: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.suggestion-chip {
    background: #F1F5F9;
    color: #475569;
    border: 1px solid #E2E8F0;
    border-radius: 16px;
    padding: 5px 12px;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
}

.suggestion-chip:hover {
    background: #634AE2;
    color: #FFFFFF;
    border-color: #634AE2;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(99, 74, 226, 0.25);
}

/* Typing Indicator Animation */
.chatbot-typing-area {
    padding: 0 16px 8px 16px;
    background: #F8FAFC;
    flex-shrink: 0;
}

.typing-bubble {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 16px 16px 16px 4px;
    padding: 10px 14px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.typing-dot {
    width: 6px;
    height: 6px;
    background-color: #634AE2;
    border-radius: 50%;
    animation: typingBounce 1.4s infinite ease-in-out both;
}

.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes typingBounce {
    0%, 80%, 100% { transform: scale(0); opacity: 0.4; }
    40% { transform: scale(1); opacity: 1; }
}

/* Footer & Input */
.chatbot-footer {
    background: #FFFFFF;
    padding: 12px 14px;
    border-top: 1px solid #E2E8F0;
    flex-shrink: 0;
}

.chatbot-input {
    flex: 1;
    background: #F1F5F9;
    border: 1.5px solid transparent;
    border-radius: 22px;
    padding: 9px 16px;
    font-size: 13.5px;
    color: #1E293B;
    outline: none;
    transition: all 0.2s;
}

.chatbot-input:focus {
    background: #FFFFFF;
    border-color: #634AE2;
    box-shadow: 0 0 0 3px rgba(99, 74, 226, 0.15);
}

.chatbot-input::placeholder {
    color: #94A3B8;
    font-size: 13px;
}

.chatbot-send-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, #634AE2 0%, #855CE8 100%);
    box-shadow: 0 4px 12px rgba(99, 74, 226, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.2s;
}

.chatbot-send-btn:hover {
    transform: scale(1.08);
    background: linear-gradient(135deg, #5339CE 0%, #7648E0 100%);
    box-shadow: 0 6px 16px rgba(99, 74, 226, 0.45);
}

.chatbot-send-btn:active {
    transform: scale(0.95);
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const chatbotBtn = document.getElementById('chatbotBtn');
    const chatbotWindow = document.getElementById('chatbotWindow');
    const closeChatbot = document.getElementById('closeChatbot');
    const clearChatbot = document.getElementById('clearChatbot');
    const chatbotForm = document.getElementById('chatbotForm');
    const chatbotInput = document.getElementById('chatbotInput');
    const chatbotMessages = document.getElementById('chatbotMessages');
    const typingIndicator = document.getElementById('typingIndicator');
    const chatbotSuggestions = document.getElementById('chatbotSuggestions');

    // Open/Close toggle
    function openChat() {
        if (!chatbotWindow) return;
        chatbotWindow.classList.remove('d-none');
        chatbotWindow.style.display = 'flex';
        if (chatbotBtn) chatbotBtn.style.display = 'none';
        if (chatbotInput) chatbotInput.focus();
        if (chatbotMessages) chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function closeChat() {
        if (!chatbotWindow) return;
        chatbotWindow.classList.add('d-none');
        chatbotWindow.style.display = 'none';
        if (chatbotBtn) chatbotBtn.style.display = 'flex';
    }

    // Expose globally so navbar/sidebar 'Messages' buttons trigger it
    window.toggleChatbot = function(prefill = '') {
        if (!chatbotWindow) return;
        if (chatbotWindow.classList.contains('d-none') || chatbotWindow.style.display === 'none') {
            openChat();
            if (prefill && chatbotInput && chatbotForm) {
                chatbotInput.value = prefill;
                chatbotForm.dispatchEvent(new Event('submit'));
            }
        } else {
            closeChat();
        }
    };

    if (chatbotBtn) chatbotBtn.addEventListener('click', openChat);
    if (closeChatbot) closeChatbot.addEventListener('click', closeChat);

    // Reset Chat
    if (clearChatbot) {
        clearChatbot.addEventListener('click', function() {
            if (confirm('Mulai percakapan baru dengan AI Assistant?')) {
                // Keep welcome message & suggestions
                const rows = chatbotMessages.querySelectorAll('.chat-row:not(:first-of-type)');
                rows.forEach(r => r.remove());
                if (chatbotSuggestions) chatbotSuggestions.classList.remove('d-none');
            }
        });
    }

    // Format Markdown safely
    function formatMessageContent(rawText) {
        if (!rawText) return '';
        
        // 1. HTML entities escape
        let text = rawText
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

        // 2. Bold text **something**
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        
        // 3. Italic text *something*
        text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');

        // 4. Clean line breaks
        text = text.replace(/\n\n/g, '</p><p class="mb-2">');
        text = text.replace(/\n/g, '<br>');

        return `<p class="mb-0">${text}</p>`;
    }

    // Add message bubble
    function addMessage(msg, isUser = false) {
        if (!chatbotMessages) return;

        const row = document.createElement('div');
        row.className = isUser ? 'chat-row user-row' : 'chat-row bot-row';

        if (!isUser) {
            const avatar = document.createElement('div');
            avatar.className = 'chat-avatar-mini';
            avatar.innerHTML = '<i class="bi bi-robot"></i>';
            row.appendChild(avatar);
        }

        const bubble = document.createElement('div');
        bubble.className = isUser ? 'chat-bubble user-bubble' : 'chat-bubble bot-bubble';

        if (isUser) {
            const textEscaped = msg
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/\n/g, '<br>');
            bubble.innerHTML = `<p class="mb-0">${textEscaped}</p>`;
        } else {
            bubble.innerHTML = formatMessageContent(msg);
        }

        row.appendChild(bubble);
        chatbotMessages.appendChild(row);

        // Auto scroll to bottom
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    // Send query to AI
    function submitQuery(queryText) {
        const message = queryText.trim();
        if (!message) return;

        // Hide suggestions once user starts chatting
        if (chatbotSuggestions) {
            chatbotSuggestions.classList.add('d-none');
        }

        // Add user message
        addMessage(message, true);
        if (chatbotInput) chatbotInput.value = '';

        // Show typing indicator
        if (typingIndicator) typingIndicator.classList.remove('d-none');
        if (chatbotMessages) chatbotMessages.scrollTop = chatbotMessages.scrollHeight;

        // Call backend ChatbotController
        fetch('<?= BASEURL ?>/chatbot/sendMessage', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            if (typingIndicator) typingIndicator.classList.add('d-none');
            if (data.status === 'success') {
                addMessage(data.reply, false);
            } else {
                addMessage('⚠️ Maaf, terjadi kendala: ' + (data.message || 'Coba sesaat lagi.'), false);
            }
        })
        .catch(error => {
            if (typingIndicator) typingIndicator.classList.add('d-none');
            addMessage('⚠️ Maaf, gagal menghubungi server AI. Pastikan server Apache / koneksi internet aktif.', false);
            console.error('Chatbot error:', error);
        });
    }

    // Handle Form Submit
    if (chatbotForm) {
        chatbotForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (chatbotInput) submitQuery(chatbotInput.value);
        });
    }

    // Handle Suggestion Chips
    document.addEventListener('click', function(e) {
        const chip = e.target.closest('.suggestion-chip');
        if (chip) {
            const query = chip.getAttribute('data-query');
            if (query) {
                submitQuery(query);
            }
        }
    });
});
</script>
