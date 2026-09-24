<?php

declare(strict_types=1);

/**
 * Tests that an existing but unreadable configuration file is reported with
 * a FileException instead of being treated as "not found".
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\Config;
use SugiPHP\Config\DirectoryConfig;
use SugiPHP\Config\Exception\FileException;
use SugiPHP\Config\FileConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UnreadableFileTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            $this->markTestSkipped('root can read files regardless of permissions');
        }

        $this->dir = sys_get_temp_dir() . '/sugi-unreadable-' . uniqid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        if (!isset($this->dir)) {
            return;
        }
        foreach (glob("{$this->dir}/*") as $file) {
            chmod($file, 0600);
            unlink($file);
        }
        rmdir($this->dir);
    }

    public static function formats(): array
    {
        return [
            'php'  => ['php', '<?php return ["key" => "value"];'],
            'ini'  => ['ini', 'key = value'],
            'json' => ['json', '{"key": "value"}'],
            'xml'  => ['xml', '<config><key>value</key></config>'],
        ];
    }

    private function createUnreadable(string $ext, string $content): string
    {
        $file = "{$this->dir}/conf.{$ext}";
        file_put_contents($file, $content);
        chmod($file, 0000);

        return $file;
    }

    #[DataProvider('formats')]
    public function testFileConfigThrows(string $ext, string $content)
    {
        $file = $this->createUnreadable($ext, $content);

        $this->expectException(FileException::class);
        new FileConfig($file);
    }

    #[DataProvider('formats')]
    public function testDirectoryConfigThrows(string $ext, string $content)
    {
        $this->createUnreadable($ext, $content);
        $config = new DirectoryConfig($this->dir);

        $this->expectException(FileException::class);
        $config->get("conf.key");
    }

    #[DataProvider('formats')]
    public function testConfigThrows(string $ext, string $content)
    {
        $file = $this->createUnreadable($ext, $content);

        $this->expectException(FileException::class);
        new Config($file);
    }
}
