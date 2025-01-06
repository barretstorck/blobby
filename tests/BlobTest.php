<?php

namespace BarretStorck\Blobby\Tests;

use BarretStorck\Blobby\Blob;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class BlobTest extends TestCase
{
    public function testConstructorWithoutParamters(): void
    {
        // Given
        // Nothing

        // When
        $blob = new Blob();

        // Then
        $this->assertInstanceOf(
            expected: Blob::class,
            actual: $blob,
        );
    }

    public function testOpen(): void
    {
        // Given
        $filename = 'php://temp';
        $mode = 'r+';

        // When
        $blob = Blob::open($filename, $mode);

        // Then
        $this->assertInstanceOf(
            expected: Blob::class,
            actual: $blob,
        );
    }
}
