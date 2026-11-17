<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use AiService;

/**
 * Unit Test untuk Guardrail & Kontrol Konteks AI Assistant Toko
 * Menguji pencegahan topik di luar konteks toko (Out of Context / OOT) seperti permintaan coding,
 * resep masakan, tugas akademik, serta memastikan pertanyaan katalog toko tetap diizinkan.
 */
class AiGuardrailTest extends TestCase {

    public function testDetectOutOfContextBlocksCodingRequests() {
        $prompt = 'Tolong buatkan kodingan python untuk scraping web';
        $refusal = AiService::detectOutOfContext($prompt, 'customer');

        $this->assertNotNull($refusal, 'Permintaan coding harus terdeteksi sebagai OOT (Out of Context)');
        $this->assertStringContainsString('tidak dapat melayani penulisan coding', $refusal);
    }

    public function testDetectOutOfContextBlocksCookingRecipeRequests() {
        $prompt = 'Bagaimana resep masakan nasi goreng spesial?';
        $refusal = AiService::detectOutOfContext($prompt, 'customer');

        $this->assertNotNull($refusal, 'Permintaan resep masakan harus terdeteksi dan ditolak secara sopan');
    }

    public function testDetectOutOfContextBlocksAcademicHomework() {
        $prompt = 'Tolong kerjakan tugas fisika materi gelombang elektromagnetik ini';
        $refusal = AiService::detectOutOfContext($prompt, 'customer');

        $this->assertNotNull($refusal, 'Pertanyaan tugas sekolah/kuliah di luar toko harus dicegah');
    }

    public function testDetectOutOfContextAllowsValidShopInquiries() {
        // Pertanyaan seputar produk toko nnaco.sa tidak boleh ditolak (harus null)
        $inquiry1 = 'Apakah ada kalung titanium mutiara yang sedang promo?';
        $this->assertNull(AiService::detectOutOfContext($inquiry1, 'customer'), 'Pertanyaan katalog produk sah harus diizinkan');

        $inquiry2 = 'Berapa harga jepit rambut aksesoris wanita?';
        $this->assertNull(AiService::detectOutOfContext($inquiry2, 'customer'), 'Pertanyaan harga aksesoris harus diizinkan');

        $inquiry3 = 'Apakah pesanan saya bisa dikirim ke daerah Cirebon?';
        $this->assertNull(AiService::detectOutOfContext($inquiry3, 'customer'), 'Pertanyaan pengiriman harus diizinkan');
    }

    public function testFormatRefusalMessagePersonalizationByRole() {
        // Pesan untuk Owner harus menyebut sapaan khas Owner
        $ownerMsg = AiService::formatRefusalMessage('coding', 'owner');
        $this->assertStringContainsString('Halo Bos', $ownerMsg, 'Pesan penolakan untuk role owner harus disesuaikan');

        // Pesan untuk Admin harus menyapa Admin
        $adminMsg = AiService::formatRefusalMessage('coding', 'admin');
        $this->assertStringContainsString('Halo Admin', $adminMsg, 'Pesan penolakan untuk role admin harus disesuaikan');

        // Pesan untuk Customer
        $customerMsg = AiService::formatRefusalMessage('coding', 'customer');
        $this->assertStringContainsString('AI Assistant resmi nnaco.sa', $customerMsg, 'Pesan untuk customer harus menyertakan nama toko');
    }
}
