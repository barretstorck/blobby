<?php

namespace BarretStorck\Blobby\Tests\Source;

use BarretStorck\Blobby\Blob;
use BarretStorck\Blobby\Sources\FileSystem;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 *
 */
class FileSystemTest extends TestCase
{
    /**
     *
     */
    public function testOpen(): void
    {
        // Given
        $path = tempnam(sys_get_temp_dir(), '');
        $data = 'Hello world';
        file_put_contents($path, $data);
        $source = new FileSystem($path);

        // When
        $blob = $source->open();

        // Then
        $this->assertInstanceOf(
            expected: Blob::class,
            actual: $blob,
        );

        $this->assertEquals(
            expected: $data,
            actual: $blob->read(),
        );

        // Cleanup
        unlink($path);
    }

    /**
     *
     */
    public function testSave(): void
    {
        // Given
        $path = tempnam(sys_get_temp_dir(), '');
        $data = 'Hello world';
        $source = new FileSystem($path);
        $blob = new Blob();
        $blob->write($data)->rewind();

        // When
        $actual = $source->save($blob);

        // Then
        $this->assertTrue($actual);

        $this->assertFileExists($path);

        $this->assertEquals(
            expected: $data,
            actual: file_get_contents($path),
        );

        // Cleanup
        unlink($path);
    }

    /**
     *
     */
    public function testSaveToTempFile(): void
    {
        // Given
        $data = 'Hello world';
        $source = new FileSystem();
        $blob = new Blob();
        $blob->write($data)->rewind();

        // When
        $actual = $source->save($blob);

        // Then
        $path = $source->getPath();
        $this->assertTrue($actual);

        $this->assertFileExists($path);

        $this->assertEquals(
            expected: $data,
            actual: file_get_contents($path),
        );

        // Cleanup
        unlink($path);
    }

    /**
     *
     */
    public function testDelete(): void
    {
        // Given
        $path = tempnam(sys_get_temp_dir(), '');
        touch($path);
        $source = new FileSystem($path);

        // When
        $actual = $source->delete();

        // Then
        $this->assertTrue($actual);

        $this->assertFileDoesNotExist($path);
    }

    /**
     *
     */
    public function testSerializeAndUnserialize(): void
    {
        // Given
        $path = tempnam(sys_get_temp_dir(), '');
        $source = new FileSystem($path);

        // When
        $serialized = serialize($source);
        $unserialized = unserialize($serialized);

        // Then
        $this->assertEquals(
            expected: $source,
            actual: $unserialized,
        );
    }
}
