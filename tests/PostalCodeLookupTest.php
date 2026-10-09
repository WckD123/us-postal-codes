<?php

namespace WckD123\UsPostalCodes\Tests;

use WckD123\UsPostalCodes\PostalCodeLookup;
use PHPUnit\Framework\TestCase;

class PostalCodeLookupTest extends TestCase
{
    protected string $fixtureDirectory;

    protected string $emptyDirectory;

    protected PostalCodeLookup $lookup;

    protected function setUp(): void
    {
        $this->fixtureDirectory = sys_get_temp_dir() . '/postal-fixture-' . uniqid();
        $this->emptyDirectory   = sys_get_temp_dir() . '/postal-empty-' . uniqid();

        mkdir($this->emptyDirectory, 0755, true);

        $this->writeFixtureFile('us/941.php', ['94105' => $this->entry('CA', ['San Francisco'])]);
        $this->writeFixtureFile('us/006.php', ['00601' => $this->entry('PR', ['Adjuntas'])]);

        // Each file below is what a malformed code would reach if find() cut or built the path
        // before checking the whole code, so a missing guard returns a match.
        $this->writeFixtureFile('us/123.php', ['12345' => $this->entry('NY', ['Cut From 123456'])]);
        $this->writeFixtureFile('.php', ['../x1' => $this->entry('CA', ['Path Traversal'])]);

        $this->lookup = new PostalCodeLookup($this->fixtureDirectory);
    }

    protected function tearDown(): void
    {
        deleteDirectory($this->fixtureDirectory);
        deleteDirectory($this->emptyDirectory);
    }

    public function testFindsAUsZip(): void
    {
        // Act
        $result = $this->lookup->find('94105');

        // Assert
        $this->assertSame('CA', $result[PostalCodeLookup::STATE]);
        $this->assertSame(['San Francisco'], $result[PostalCodeLookup::CITIES]);
    }

    public function testFindsAUsZipPlusFour(): void
    {
        // Act
        $result = $this->lookup->find(' 94105-1234 ');

        // Assert
        $this->assertSame('CA', $result[PostalCodeLookup::STATE]);
    }

    public function testFindsATerritoryZip(): void
    {
        // Act
        $result = $this->lookup->find('00601');

        // Assert
        $this->assertSame('PR', $result[PostalCodeLookup::STATE]);
        $this->assertSame('Adjuntas', $result[PostalCodeLookup::CITIES][0]);
    }

    public function testDefaultLookupReadsTheShippedUsData(): void
    {
        // Act
        $result = (new PostalCodeLookup())->find('94105');

        // Assert
        $this->assertSame('CA', $result[PostalCodeLookup::STATE]);
    }

    public function testValidZipReturnsNullWhenItsPrefixFileIsMissing(): void
    {
        // Act
        $result = (new PostalCodeLookup($this->emptyDirectory))->find('94105');

        // Assert
        $this->assertNull($result);
    }

    public function testUnknownCodeReturnsNull(): void
    {
        // Act
        $result = $this->lookup->find('94199');

        // Assert
        $this->assertNull($result);
    }

    public function testMalformedCodesReturnNull(): void
    {
        // Assert
        $this->assertNull($this->lookup->find('../x1'));
        $this->assertNull($this->lookup->find('123456'));
        $this->assertNull($this->lookup->find('1234'));
    }

    protected function entry(string $state, array $cities): array
    {
        return [PostalCodeLookup::STATE => $state, PostalCodeLookup::CITIES => $cities];
    }

    protected function writeFixtureFile(string $relativePath, array $entries): void
    {
        $path = $this->fixtureDirectory . '/' . $relativePath;

        @mkdir(\dirname($path), 0755, true);

        file_put_contents($path, '<?php return ' . var_export($entries, true) . ';');
    }
}
