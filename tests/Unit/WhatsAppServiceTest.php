<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use WhatsAppService;

/**
 * Unit Test untuk Layanan WhatsApp OTP Service
 * Menguji normalisasi nomor handphone, validasi format nomor Indonesia, dan masking privasi UI.
 */
class WhatsAppServiceTest extends TestCase {

    public function testNormalizePhoneWithZeroPrefix() {
        $result = WhatsAppService::normalizePhone('081234567890');
        $this->assertEquals('6281234567890', $result, 'Nomor berawalan 0 harus dinormalisasi menjadi 628...');
    }

    public function testNormalizePhoneWithEightPrefix() {
        $result = WhatsAppService::normalizePhone('81234567890');
        $this->assertEquals('6281234567890', $result, 'Nomor berawalan 8 langsung harus dinormalisasi menjadi 628...');
    }

    public function testNormalizePhoneWithInternationalFormat() {
        $result = WhatsAppService::normalizePhone('+62 812-3456-7890');
        $this->assertEquals('6281234567890', $result, 'Nomor berformat +62 dengan spasi dan tanda hubung harus dibersihkan');
    }

    public function testNormalizePhoneAlreadyNormalized() {
        $result = WhatsAppService::normalizePhone('6285712345678');
        $this->assertEquals('6285712345678', $result, 'Nomor yang sudah 628 harus tetap dipertahankan');
    }

    public function testNormalizePhoneEmptyOrInvalid() {
        $result = WhatsAppService::normalizePhone('');
        $this->assertEquals('', $result, 'Input kosong harus mengembalikan string kosong');
    }

    public function testIsValidIndonesianPhoneValidNumbers() {
        $this->assertTrue(WhatsAppService::isValidIndonesianPhone('081234567890'), 'Format 0812 harus valid');
        $this->assertTrue(WhatsAppService::isValidIndonesianPhone('6285712345678'), 'Format 62857 harus valid');
        $this->assertTrue(WhatsAppService::isValidIndonesianPhone('+628991234567'), 'Format +62899 harus valid');
    }

    public function testIsValidIndonesianPhoneInvalidNumbers() {
        $this->assertFalse(WhatsAppService::isValidIndonesianPhone('0217654321'), 'Nomor telepon rumah 021 tidak boleh dianggap nomor seluler valid');
        $this->assertFalse(WhatsAppService::isValidIndonesianPhone('12345'), 'Nomor terlalu pendek harus ditolak');
        $this->assertFalse(WhatsAppService::isValidIndonesianPhone('abcdefghijk'), 'Karakter non-angka harus ditolak');
        $this->assertFalse(WhatsAppService::isValidIndonesianPhone(''), 'String kosong harus ditolak');
    }

    public function testMaskPhoneForPrivacy() {
        $masked = WhatsAppService::maskPhone('081234567890');
        // Nomor normal 13 digit: 6281••••7890
        $this->assertStringStartsWith('6281', $masked, 'Awalan nomor harus tetap terlihat untuk identifikasi');
        $this->assertStringEndsWith('7890', $masked, 'Akhiran nomor harus tetap terlihat untuk konfirmasi user');
        $this->assertStringContainsString('••••', $masked, 'Bagian tengah nomor wajib disamarkan demi privasi');
    }

    public function testMaskPhoneShortInput() {
        $masked = WhatsAppService::maskPhone('12345');
        $this->assertEquals('12345', $masked, 'Nomor yang terlalu pendek tidak boleh dirusak fungsi masking');
    }
}
