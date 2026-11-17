<?php
/**
 * Nnaco.sa Standalone Local Test Runner
 * ----------------------------------------------------
 * Skrip ini memungkinkan Anda menjalankan seluruh Unit Test secara lokal
 * menggunakan PHP bawaan XAMPP tanpa wajib menginstall Composer terlebih dahulu.
 * 
 * Penggunaan:
 *   php run_tests.php
 *   atau
 *   C:\xampp\php\php.exe run_tests.php
 */

namespace PHPUnit\Framework {
    if (!class_exists('PHPUnit\Framework\TestCase')) {
        class AssertionFailedException extends \Exception {}

        abstract class TestCase {
            public static $assertionsCount = 0;

            protected function setUp(): void {}
            protected function tearDown(): void {}

            protected function assertTrue($condition, $message = '') {
                self::$assertionsCount++;
                if ($condition !== true) {
                    throw new AssertionFailedException($message ?: 'Failed asserting that condition is true.');
                }
            }

            protected function assertFalse($condition, $message = '') {
                self::$assertionsCount++;
                if ($condition !== false) {
                    throw new AssertionFailedException($message ?: 'Failed asserting that condition is false.');
                }
            }

            protected function assertEquals($expected, $actual, $message = '') {
                self::$assertionsCount++;
                if ($expected != $actual) {
                    $expStr = is_scalar($expected) ? (string)$expected : json_encode($expected);
                    $actStr = is_scalar($actual) ? (string)$actual : json_encode($actual);
                    throw new AssertionFailedException($message ?: "Failed asserting that '$actStr' equals expected '$expStr'.");
                }
            }

            protected function assertSame($expected, $actual, $message = '') {
                self::$assertionsCount++;
                if ($expected !== $actual) {
                    throw new AssertionFailedException($message ?: 'Failed asserting that two variables reference the same exact value and type.');
                }
            }

            protected function assertNull($actual, $message = '') {
                self::$assertionsCount++;
                if ($actual !== null) {
                    throw new AssertionFailedException($message ?: 'Failed asserting that value is null.');
                }
            }

            protected function assertNotNull($actual, $message = '') {
                self::$assertionsCount++;
                if ($actual === null) {
                    throw new AssertionFailedException($message ?: 'Failed asserting that value is not null.');
                }
            }

            protected function assertIsArray($actual, $message = '') {
                self::$assertionsCount++;
                if (!is_array($actual)) {
                    throw new AssertionFailedException($message ?: 'Failed asserting that value is an array.');
                }
            }

            protected function assertIsString($actual, $message = '') {
                self::$assertionsCount++;
                if (!is_string($actual)) {
                    throw new AssertionFailedException($message ?: 'Failed asserting that value is a string.');
                }
            }

            protected function assertEmpty($actual, $message = '') {
                self::$assertionsCount++;
                if (!empty($actual)) {
                    throw new AssertionFailedException($message ?: 'Failed asserting that value is empty.');
                }
            }

            protected function assertNotEmpty($actual, $message = '') {
                self::$assertionsCount++;
                if (empty($actual)) {
                    throw new AssertionFailedException($message ?: 'Failed asserting that value is not empty.');
                }
            }

            protected function assertCount($expectedCount, $countable, $message = '') {
                self::$assertionsCount++;
                $count = is_countable($countable) ? count($countable) : 0;
                if ($count !== $expectedCount) {
                    throw new AssertionFailedException($message ?: "Failed asserting that count $count matches expected $expectedCount.");
                }
            }

            protected function assertStringContainsString($needle, $haystack, $message = '') {
                self::$assertionsCount++;
                if (strpos((string)$haystack, (string)$needle) === false) {
                    throw new AssertionFailedException($message ?: "Failed asserting that '$haystack' contains '$needle'.");
                }
            }

            protected function assertStringStartsWith($prefix, $string, $message = '') {
                self::$assertionsCount++;
                if (!str_starts_with((string)$string, (string)$prefix)) {
                    throw new AssertionFailedException($message ?: "Failed asserting that '$string' starts with '$prefix'.");
                }
            }

            protected function assertStringEndsWith($suffix, $string, $message = '') {
                self::$assertionsCount++;
                if (!str_ends_with((string)$string, (string)$suffix)) {
                    throw new AssertionFailedException($message ?: "Failed asserting that '$string' ends with '$suffix'.");
                }
            }
        }
    }
}

namespace {
    require_once __DIR__ . '/tests/bootstrap.php';

    use PHPUnit\Framework\TestCase;
    use PHPUnit\Framework\AssertionFailedException;

    echo "\n=======================================================\n";
    echo "       NNACO.SA - AUTOMATED UNIT TEST RUNNER          \n";
    echo "           Rekayasa Perangkat Lunak (UAS)             \n";
    echo "=======================================================\n\n";

    // Muat seluruh file test di tests/Unit/
    $testFiles = glob(__DIR__ . '/tests/Unit/*Test.php');
    foreach ($testFiles as $file) {
        require_once $file;
    }

    $testClasses = [
        \Tests\Unit\WhatsAppServiceTest::class,
        \Tests\Unit\CsrfTokenTest::class,
        \Tests\Unit\AiGuardrailTest::class,
        \Tests\Unit\UrlParserTest::class,
    ];

    $totalTests = 0;
    $totalPassed = 0;
    $failures = [];
    $startTime = microtime(true);

    foreach ($testClasses as $className) {
        $shortName = (new \ReflectionClass($className))->getShortName();
        echo "• Running Suite: {$shortName}\n";

        $methods = (new \ReflectionClass($className))->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (str_starts_with($method->name, 'test')) {
                $totalTests++;
                $testInstance = new $className();

                try {
                    // Call setUp
                    $setUpMethod = new \ReflectionMethod($testInstance, 'setUp');
                    $setUpMethod->setAccessible(true);
                    $setUpMethod->invoke($testInstance);

                    // Run test
                    $method->invoke($testInstance);

                    // Call tearDown
                    $tearDownMethod = new \ReflectionMethod($testInstance, 'tearDown');
                    $tearDownMethod->setAccessible(true);
                    $tearDownMethod->invoke($testInstance);

                    $totalPassed++;
                    echo "   [PASS] {$method->name}\n";
                } catch (AssertionFailedException $e) {
                    echo "   [FAIL] {$method->name} (FAILED)\n";
                    $failures[] = [
                        'class' => $shortName,
                        'method' => $method->name,
                        'message' => $e->getMessage(),
                    ];
                } catch (\Throwable $e) {
                    echo "   [FAIL] {$method->name} (ERROR: {$e->getMessage()})\n";
                    $failures[] = [
                        'class' => $shortName,
                        'method' => $method->name,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }
        echo "\n";
    }

    $duration = round(microtime(true) - $startTime, 4);

    echo "-------------------------------------------------------\n";
    echo "Hasil Pengujian:\n";
    echo "  Total Tests   : {$totalTests}\n";
    echo "  Passed (Lolos): {$totalPassed}\n";
    echo "  Failed (Gagal): " . count($failures) . "\n";
    echo "  Assertions    : " . TestCase::$assertionsCount . "\n";
    echo "  Waktu Eksekusi: {$duration} detik\n";
    echo "-------------------------------------------------------\n";

    if (count($failures) === 0) {
        echo "STATUS: [ SUCCESS - ALL TESTS PASSED ] (Status Hijau / Lolos 100%)\n\n";
        exit(0);
    } else {
        echo "STATUS: [ FAILED ] Terdapat pengujian yang gagal:\n";
        foreach ($failures as $f) {
            echo " - [{$f['class']}::{$f['method']}]: {$f['message']}\n";
        }
        echo "\n";
        exit(1);
    }
}
