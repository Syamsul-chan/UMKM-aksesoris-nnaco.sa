<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App;

/**
 * Helper class untuk menguji method parseURL() dari App tanpa memicu eksekusi controller
 */
class TestableApp extends App {
    public function __construct() {
        // Sengaja kosong untuk isolasi unit test
    }
}

/**
 * Unit Test untuk URL Routing Parser
 * Menguji sanitasi query string dan pemecahan URL menjadi parameter controller & method.
 */
class UrlParserTest extends TestCase {

    private $app;

    protected function setUp(): void {
        parent::setUp();
        $this->app = new TestableApp();
    }

    public function testParseUrlWithControllerAndMethod() {
        $_GET['url'] = 'admin/products';
        $parsed = $this->app->parseURL();

        $this->assertIsArray($parsed);
        $this->assertCount(2, $parsed);
        $this->assertEquals('admin', $parsed[0]);
        $this->assertEquals('products', $parsed[1]);
    }

    public function testParseUrlWithParameters() {
        $_GET['url'] = 'admin/productEdit/42/details';
        $parsed = $this->app->parseURL();

        $this->assertCount(4, $parsed);
        $this->assertEquals('admin', $parsed[0]);
        $this->assertEquals('productEdit', $parsed[1]);
        $this->assertEquals('42', $parsed[2]);
        $this->assertEquals('details', $parsed[3]);
    }

    public function testParseUrlTrimsTrailingSlash() {
        $_GET['url'] = 'home/index///';
        $parsed = $this->app->parseURL();

        $this->assertCount(2, $parsed, 'Garis miring di akhir URL harus di-trim dengan bersih');
        $this->assertEquals(['home', 'index'], $parsed);
    }

    public function testParseUrlWhenEmptyReturnsEmptyArray() {
        unset($_GET['url']);
        $parsed = $this->app->parseURL();

        $this->assertIsArray($parsed);
        $this->assertEmpty($parsed, 'URL yang tidak ada harus mengembalikan array kosong');
    }
}
