<?php

namespace BarretStorck\Blobby;

use InvalidArgumentException;
use Generator;

/**
 *
 */
class Blob
{
    const DEFAULT_CHUNK_SIZE = 8192;

    protected $resource;
    protected int $chunkSize;

    /**
     *
     */
    public function __construct($resource = null, null|int $chunkSize = null)
    {
        // If the constructor wasn't given an existing file resource
        // then create one using PHP's temporary space.
        if (!is_resource($resource)) {
            $resource = fopen('php://temp', 'r+');
        }

        $this->resource = $resource;
        $this->setChunkSize($chunkSize);
    }

    /**
     *
     */
    public static function open(string $filename, string $mode, null|int $chunkSize = null, bool $use_include_path = false, $context = null): static
    {
        $resource = fopen($filename, $mode, $use_include_path, $context);
        return new static($resource, $chunkSize);
    }

    /**
     *
     */
    public function setChunkSize(null|int $input = null): self
    {
        if (is_null($input) || $input <= 0) {
            $input = static::DEFAULT_CHUNK_SIZE;
        }

        $this->chunkSize = $input;
        return $this;
    }

    /**
     *
     */
    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * Parses the Blob data's current line as a CSV into an array.
     * https://www.php.net/manual/en/function.fgetcsv.php
     */
    public function getcsv(null|int $length = null, string $separator = ',', string $enclosure = '"', string $escape = '\\'): array
    {
        return fgetcsv($this->resource, $length, $separator, $enclosure, $escape);
    }

    /**
     *
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Closes the resource for the Blob data.
     * https://www.php.net/manual/en/function.fclose.php
     */
    public function close(): bool
    {
        return fclose($this->resource);
    }

    /**
     * Moves the Blob's data pointer to the end of the data.
     */
    public function end(): self
    {
        $this->seek(0, SEEK_END);
        return $this;
    }

    /**
     * Check if the data pointer is at the end of the Blob
     * https://www.php.net/manual/en/function.feof.php
     */
    public function eof(): bool
    {
        return feof($this->resource);
    }

    /**
     * Formats a given array of values into a CSV formatted line and writes it
     * to the Blob's data.
     * https://www.php.net/manual/en/function.fputcsv.php
     */
    public function putcsv(array $fields, string $separator = ',', string $enclosure = '\"', string $escape = '\\', string $eol = "\n"): int
    {
        return fputcsv($this->resource, $fields, $separator, $enclosure, $escape, $eol);
    }

    /**
     * Read data from the Blob into a string.
     * https://www.php.net/manual/en/function.fread.php
     */
    public function read(null|int $length = null): string
    {
        return fread($this->resource, $length ?? $this->getChunkSize());
    }

    /**
     * Returns the number of bytes remaining until the end of the Blob data.
     */
    public function remaining(): int
    {
        $originalPosition = $this->tell();
        $this->end();
        $endPosition = $this->tell();
        $this->seek($originalPosition);
        return $endPosition - $originalPosition;
    }

    /**
     * Moves the Blob data pointer to the beginning of the data.
     * https://www.php.net/manual/en/function.rewind.php
     */
    public function rewind(): self
    {
        rewind($this->resource);
        return $this;
    }

    /**
     * Moves the Blob's data pointer to the offset.
     * https://www.php.net/manual/en/function.fseek.php
     */
    public function seek(int $offset, int $whence = SEEK_SET): self
    {
        fseek($this->resource, $offset, $whence);
        return $this;
    }

    /**
     * Returns the total size of the Blob's data in bytes.
     */
    public function size(): int
    {
        $originalPosition = $this->tell();
        $this->end();
        $size = $this->tell();
        $this->seek($originalPosition);
        return $size;
    }

    /**
     * Returns the Blob data pointer's current position.
     * https://www.php.net/manual/en/function.ftell.php
     */
    public function tell(): int
    {
        return ftell($this->resource);
    }

    /**
     *
     */
    public function truncate(int $size = 0): self
    {
        ftruncate($this->resource, $size);
        return $this;
    }

    /**
     * Write string data into the Blob.
     * https://www.php.net/manual/en/function.fwrite.php
     */
    public function write($input, null|int $length = null): int
    {
        if (!is_null($length) && $length <= 0) {
            throw new InvalidArgumentException('Blob::write() requires $length to be null or a positive integer');
        }

        if (is_string($input)) {
            return fwrite($this->resource, $input, $length);
        }

        if ($input instanceof Blob) {
            $input = $input->getResource();
        }

        if (is_resource($input)) {
            $bytes = 0;
            while (!feof($input) && (is_null($length) || $bytes < $length)) {
                $bufferSize = $this->getChunkSize();
                if (!is_null($length)) {
                    $remainingBytes = $length - $bytes;
                    $bufferSize = min($bufferSize, $remainingBytes);
                }

                $buffer = fread($input, $bufferSize);
                $bytes += fwrite($this->resource, $buffer);
            }
            return $bytes;
        }

        throw new InvalidArgumentException('Blob::write() requires $input to be aa string, Blob, or resource type.');
    }

    /**
     * Split the Blob data into chunks.
     */
    public function toChunks(null|int $size = null): Generator
    {
        while (!$this->eof()) {
            yield $this->read($size ?? $this->getChunkSize());
        }
    }
}
