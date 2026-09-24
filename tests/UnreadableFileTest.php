<?php

declare(strict_types=1);

/**
 * Tests that an existing but unreadable configuration file is reported with
 * a FileException, regardless of how the loader was constructed, instead of
 * being treated as "not found".
 */

namespace SugiPHP\Config\Tests;

use SugiPHP\Config\Exception\FileException;
use SugiPHP\Config\Loader\IniLoader;
use SugiPHP\Config\Loader\JsonLoader;
use SugiPHP\Config\Loader\PhpLoader;
use SugiPHP\Config\Loader\XmlLoader;
use SugiPHP\Config\LoaderConfig;
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

        $contents = [
            'php'  => '<?php return ["key" => "value"];',
            'ini'  => 'key = value',
            'json' => '{"key": "value"}',
            'xml'  => '<config><key>value</key></config>',
        ];
        foreach ($contents as $ext => $content) {
            $file = "{$this->dir}/conf.{$ext}";
            file_put_contents($file, $content);
            chmod($file, 0000);
        }
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

    public static function loaderClasses(): array
    {
        return [
            'php'  => [PhpLoader::class, 'php'],
            'ini'  => [IniLoader::class, 'ini'],
            'json' => [JsonLoader::class, 'json'],
            'xml'  => [XmlLoader::class, 'xml'],
        ];
    }

    #[DataProvider('loaderClasses')]
    public function testLoaderWithoutPathsThrows(string $class, string $ext)
    {
        $loader = new $class();

        $this->expectException(FileException::class);
        $loader->load("{$this->dir}/conf.{$ext}");
    }

    #[DataProvider('loaderClasses')]
    public function testLoaderWithPathsThrows(string $class, string $ext)
    {
        $loader = new $class($this->dir);

        $this->expectException(FileException::class);
        $loader->load('conf');
    }

    #[DataProvider('loaderClasses')]
    public function testLoaderWithPathsAndFullPathThrows(string $class, string $ext)
    {
        $loader = new $class(__DIR__ . '/config');

        $this->expectException(FileException::class);
        $loader->load("{$this->dir}/conf.{$ext}");
    }

    public function testLoaderConfigDoesNotFallThroughToNextLoader()
    {
        // conf.json is readable and would resolve "conf.key", but the
        // unreadable conf.php found first by the PHP loader must not be
        // silently skipped
        chmod("{$this->dir}/conf.json", 0600);
        $config = new LoaderConfig([new PhpLoader($this->dir), new JsonLoader($this->dir)]);

        $this->expectException(FileException::class);
        $config->get('conf.key');
    }
}
