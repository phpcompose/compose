<?php
namespace Tests\Support;

use PHPUnit\Framework\TestCase;
use Compose\Support\Configuration;
use RuntimeException;

class ConfigurationTest extends TestCase
{
    public function testGetNestedValueReturnsDefaultWhenMissing()
    {
        $cfg = new Configuration(['app' => ['name' => 'demo']]);
        $this->assertSame('n/a', $cfg->getNestedValue('app.version', 'n/a'));
    }

    public function testGetNestedValueReturnsValue()
    {
        $cfg = new Configuration(['app' => ['name' => 'demo', 'version' => '1.0']]);
        $this->assertSame('1.0', $cfg->getNestedValue('app.version'));
    }

    public function testMergeMergesArrays()
    {
        $cfg = new Configuration(['a' => ['x' => 1]]);
        $cfg->merge(['a' => ['y' => 2], 'b' => 3]);
        $this->assertSame(1, $cfg['a']['x']);
        $this->assertSame(2, $cfg['a']['y']);
        $this->assertSame(3, $cfg['b']);
    }

    public function testMergeFromFileLoadsFile()
    {
        $tmp = sys_get_temp_dir() . '/config_test_' . uniqid() . '.php';
        file_put_contents($tmp, "<?php\nreturn ['from_file' => ['ok' => true]];\n");

        $cfg = new Configuration(['existing' => true]);
        $cfg->mergeFromFile($tmp);

        $this->assertTrue($cfg['from_file']['ok']);

        @unlink($tmp);
    }

    public function testReadOnlyPreventsMutation()
    {
        $cfg = new Configuration(['a' => 1], true);
        $this->expectException(RuntimeException::class);
        $cfg['b'] = 2; // should throw
    }
}
