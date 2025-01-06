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
     *
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     *
     */
    public function close(): bool
    {
        return fclose($this->resource);
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
     * Write string data into the Blob.
     * https://www.php.net/manual/en/function.fwrite.php
     */
    public function write(string $input, null|int $length = null): int
    {
        return fwrite($this->resource, $input, $length);
    }

    /**
     * Check if the pointer is at the end of the Blob
     * https://www.php.net/manual/en/function.feof.php
     */
    public function eof(): bool
    {
        return feof($this->resource);
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
