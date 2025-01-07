<?php

namespace BarretStorck\Blobby\Tests;

use BarretStorck\Blobby\Blob;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 *
 */
class BlobTest extends TestCase
{
    /**
     *
     */
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

    /**
     *
     */
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

    /**
     *
     */
    public static function provideGetSetBufferSize(): array
    {
        return [
            'null' => [
                'given' => null,
                'expect' => Blob::DEFAULT_BUFFER_SIZE,
            ],
            'One' => [
                'given' => 1,
                'expect' => 1,
            ],
            'Zero' => [
                'given' => 0,
                'expect' => Blob::DEFAULT_BUFFER_SIZE,
            ],
            'Negative One' => [
                'given' => -1,
                'expect' => Blob::DEFAULT_BUFFER_SIZE,
            ],
            'Ten' => [
                'given' => 10,
                'expect' => 10,
            ],
        ];
    }

    /**
     *
     */
    #[DataProvider('provideGetSetBufferSize')]
    public function testGetSetBufferSize($given, $expect): void
    {
        // Given
        $blob = new Blob(bufferSize: $given);

        // When
        $actual = $blob->getBufferSize();

        // Then
        $this->assertEquals(
            expected: $expect,
            actual: $actual,
        );
    }

    /**
     *
     */
    public function testGetResource(): void
    {
        // Given
        $resource = fopen('php://temp', 'r+');
        $blob = new Blob($resource);

        // When
        $actual = $blob->getResource();

        // Then
        $this->assertSame(
            expected: $resource,
            actual: $actual,
        );
    }

    /**
     *
     */
    public function testClose(): void
    {
        // Given
        $blob = new Blob();

        // When
        $returned = $blob->close();
        $resource = $blob->getResource();

        // Then
        $this->assertTrue($returned);
        $this->assertFalse(is_resource($resource));
    }

    /**
     *
     */
    public function testEnd(): void
    {
        // Given
        $resource = fopen('php://temp', 'r+');
        $data = 'Hello world';
        fwrite($resource, $data);
        rewind($resource);
        $blob = new Blob($resource);

        // When
        $blob->end();

        // Then
        $this->assertEquals(
            expected: strlen($data),
            actual: ftell($resource),
        );
    }

    /**
     *
     */
    public function testEofTrue(): void
    {
        // Given
        $resource = fopen('php://temp', 'r+');
        $data = 'Hello world';
        fwrite($resource, $data);
        $blob = new Blob($resource);
        $blob->read(8192); // Read to guarentee we're past the last byte

        // When
        $actual = $blob->eof();

        // Then
        $this->assertTrue($actual);
    }

    /**
     *
     */
    public function testEofFalse(): void
    {
        // Given
        $resource = fopen('php://temp', 'r+');
        $data = 'Hello world';
        fwrite($resource, $data);
        rewind($resource); // Rewind so there are bytes after the pointer
        $blob = new Blob($resource);

        // When
        $actual = $blob->eof();

        // Then
        $this->assertFalse($actual);
    }

    /**
     *
     */
    public function testRead(): void
    {
        // Given
        $resource = fopen('php://temp', 'r+');
        $data = 'Hello world';
        fwrite($resource, $data);
        rewind($resource);
        $blob = new Blob($resource);

        // When
        $actual = $blob->read();

        // Then
        $this->assertEquals(
            expected: $data,
            actual: $actual,
        );
    }

    /**
     *
     */
    public function testWriteString(): void
    {
        // Given
        $data = 'Hello world';
        $blob = new Blob();

        // When
        $blob->write($data);

        // Then
        $this->assertEquals(
            expected: strlen($data),
            actual: $blob->getWriteResult(),
        );

        $this->assertEquals(
            expected: $data,
            actual: $blob->rewind()->read(),
        );
    }

    /**
     *
     */
    public function testWriteBlob(): void
    {
        // Given
        $data = 'Hello world';
        $blob1 = new Blob();
        $blob1
            ->write($data)
            ->rewind();
        $blob2 = new Blob();

        // When
        $blob2->write($blob1);

        // Then
        $this->assertEquals(
            expected: strlen($data),
            actual: $blob2->getWriteResult(),
        );

        $this->assertEquals(
            expected: $data,
            actual: $blob2->rewind()->read(),
        );
    }

    /**
     *
     */
    public function testGetCsv(): void
    {
        // Given
        $fields = [
            'foo',
            'bar',
            'fizz',
            'buzz',
        ];
        $resource = fopen('php://temp', 'r+');
        fputcsv(
            stream: $resource,
            fields: $fields,
            escape: '\\',
        );
        rewind($resource);

        $blob = new Blob($resource);

        // When
        $actual = $blob->getcsv();

        // Then
        $this->assertEquals(
            expected: $fields,
            actual: $actual,
        );
    }

    /**
     *
     */
    public function testPutCsv(): void
    {
        // Given
        $fields = [
            'foo',
            'bar',
            'fizz',
            'buzz',
        ];
        $resource = fopen('php://temp', 'r+');
        $blob = new Blob($resource);

        // When
        $blob->putcsv($fields);

        // Then
        rewind($resource);
        $actual = fgetcsv(
            stream: $resource,
            escape: '\\',
        );
        $this->assertEquals(
            expected: $fields,
            actual: $actual,
        );
    }

    /**
     *
     */
    public function testRemaining(): void
    {
        // Given
        $data = 'Hello world';
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $data);
        fseek($resource, 2);
        $blob = new Blob($resource);

        // When
        $actual = $blob->remaining();

        // Then
        $this->assertEquals(
            expected: 9,
            actual: $actual,
        );
    }

    /**
     *
     */
    public function testSize(): void
    {
        // Given
        $data = 'Hello world';
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $data);
        fseek($resource, 2);
        $blob = new Blob($resource);

        // When
        $actual = $blob->size();

        // Then
        $this->assertEquals(
            expected: 11,
            actual: $actual,
        );
    }

    /**
     *
     */
    public function testClone(): void
    {
        // Given
        $blob1 = new Blob();
        $data = 'Hello world';
        $pointer = 4;
        $blob1->write($data);
        $blob1->seek($pointer);

        // When
        $blob2 = clone $blob1;

        // Then
        // The source Blob should have it's pointer in the same position as
        // before
        $this->assertEquals(
            expected: $pointer,
            actual: $blob1->tell(),
        );

        // The destination Blob should have it's pointer in the same position as
        // the source.
        $this->assertEquals(
            expected: $pointer,
            actual: $blob2->tell(),
        );

        // Rewind both so we can fetch their full contents.
        $blob1->rewind();
        $blob2->rewind();

        // The source Blob still has it's original contents unmodified.
        $this->assertEquals(
            expected: $data,
            actual: $blob1->read(),
        );

        // The destination Blob has a complete copy of the source's contents.
        $this->assertEquals(
            expected: $data,
            actual: $blob2->read(),
        );

        // Make sure that both Blobs are different objects.
        $this->assertNotSame(
            expected: $blob1,
            actual: $blob2,
        );
    }

    /**
     *
     */
    public function testToString(): void
    {
        // Given
        $data = 'Hello world';
        $pointer = 4;
        $blob = new Blob();
        $blob->write($data);
        $blob->seek($pointer);

        // When
        $actual = strval($blob);

        // Then
        $this->assertEquals(
            expected: $data,
            actual: $actual,
        );

        $this->assertEquals(
            expected: $pointer,
            actual: $blob->tell(),
        );
    }
}
