<?php

namespace WckD123\UsPostalCodes\Tests;

use WckD123\UsPostalCodes\PostalCodeLookup;
use PHPUnit\Framework\TestCase;

class PostalCodeGeneratorTest extends TestCase
{
    protected string $tmpOut;
    protected string $tmpIn;

    protected function setUp(): void
    {
        $this->tmpOut = sys_get_temp_dir() . '/postal-out-' . uniqid();
        $this->tmpIn  = sys_get_temp_dir() . '/postal-in-' . uniqid();
    }

    protected function tearDown(): void
    {
        deleteDirectory($this->tmpOut);
        deleteDirectory($this->tmpIn);
    }

    public function testGeneratorBuildsUsDataFromTheGeonamesFiles(): void
    {
        // Act
        $result = $this->runGenerator(__DIR__ . '/fixtures/geonames');

        // Assert
        $this->assertSame(0, $result['exit']);

        $lookup = new PostalCodeLookup($this->tmpOut);

        // The first row for a ZIP wins over the FPO repeat.
        $this->assertSame(['state' => 'CA', 'city' => 'San Francisco'], $lookup->find('94105'));
        $this->assertSame(['state' => 'PR', 'city' => 'Adjuntas'], $lookup->find('00601'));

        // AA (armed forces) is not a state or territory, so it is skipped.
        $this->assertNull($lookup->find('96201'));
    }

    public function testGeneratorKeepsOtherFilesInTheOutputFolder(): void
    {
        // Arrange
        mkdir($this->tmpOut, 0755, true);
        file_put_contents($this->tmpOut . '/keep.php', '<?php');
        file_put_contents($this->tmpOut . '/999.php', '<?php return [];');

        // Act
        $this->runGenerator(__DIR__ . '/fixtures/geonames');

        // Assert
        $this->assertFileExists($this->tmpOut . '/keep.php');
        $this->assertFileDoesNotExist($this->tmpOut . '/999.php');
    }

    public function testGeneratorNamesAMissingSourceFile(): void
    {
        // Arrange
        mkdir($this->tmpIn . '/US', 0755, true);
        file_put_contents($this->tmpIn . '/US/US.txt', "US\t94105\tSan Francisco\tCalifornia\tCA\n");

        // Act
        $result = $this->runGenerator($this->tmpIn);

        // Assert
        $this->assertSame(1, $result['exit']);
        $this->assertStringContainsString('PR/PR.txt', $result['stderr']);
    }

    /**
     * @return array{exit: int, stderr: string}
     */
    protected function runGenerator(string $input): array
    {
        $command = [PHP_BINARY, \dirname(__DIR__) . '/bin/generate-postal-code-data.php', $input, $this->tmpOut];
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);

        stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        return ['exit' => proc_close($process), 'stderr' => $stderr];
    }
}
