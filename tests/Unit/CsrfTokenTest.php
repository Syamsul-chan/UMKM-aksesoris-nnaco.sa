<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Controller;

/**
 * Unit Test untuk Keamanan CSRF (Cross-Site Request Forgery)
 * Menguji pembuatan token acak berentropi tinggi dan mekanisme validasi anti-pemalsuan request.
 */
class CsrfTokenTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        $_SESSION['csrf_token'] = null;
        unset($_SESSION['csrf_token']);
    }

    public function testGenerateCsrfTokenCreatesNonEmptyString() {
        $token = Controller::generateCsrfToken();
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token), 'CSRF token 32 byte harus menghasilkan representasi heksadesimal 64 karakter');
    }

    public function testGenerateCsrfTokenConsistencyWithinSession() {
        $token1 = Controller::generateCsrfToken();
        $token2 = Controller::generateCsrfToken();
        $this->assertSame($token1, $token2, 'Dalam session yang sama, token CSRF yang dihasilkan harus konsisten');
    }

    public function testVerifyCsrfTokenWithValidToken() {
        $token = Controller::generateCsrfToken();
        $this->assertTrue(Controller::verifyCsrfToken($token), 'Verifikasi token yang sama persis harus bernilai true');
    }

    public function testVerifyCsrfTokenWithInvalidToken() {
        Controller::generateCsrfToken();
        $this->assertFalse(Controller::verifyCsrfToken('invalid_malicious_token_12345'), 'Token yang dipalsukan harus ditolak');
    }

    public function testVerifyCsrfTokenWithEmptyToken() {
        Controller::generateCsrfToken();
        $this->assertFalse(Controller::verifyCsrfToken(''), 'Token kosong tidak boleh dianggap valid');
        $this->assertFalse(Controller::verifyCsrfToken(null), 'Token null tidak boleh dianggap valid');
    }
}
