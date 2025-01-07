<?php

namespace BarretStorck\Blobby\Sources;

use BarretStorck\Blobby\Blob;
use BarretStorck\Blobby\ReadableBlobSourceInterface;
use BarretStorck\Blobby\WriteableBlobSourceInterface;
use BarretStorck\Blobby\DeleteableBlobSourceInterface;

class FileSystem implements ReadableBlobSourceInterface, WriteableBlobSourceInterface, DeleteableBlobSourceInterface
{
    protected null|string $path = null;

    /**
     *
     */
    public function __construct(null|string $path = null)
    {
        $this->setPath($path);
    }

    /**
     *
     */
    public function setPath(null|string $path = null): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     *
     */
    public function getPath(): null|string
    {
        return $this->path;
    }

    /**
     *
     */
    public function open(): Blob
    {
        $resource = fopen($this->path, 'rb');
        $blob = new Blob();
        $blob->write($resource);
        fclose($resource);
        $blob->rewind();
        return $blob;
    }

    /**
     *
     */
    public function save(Blob $blob): bool
    {
        if (is_null($this->path)) {
            $tempFileName = tempnam(sys_get_temp_dir(), '');
            $this->setPath($tempFileName);
        }

        $resource = fopen($this->path, 'wb');

        foreach ($blob->toChunks() as $chunk) {
            fwrite($resource, $chunk);
        }

        fclose($resource);
        return true;
    }

    /**
     *
     */
    public function delete(): bool
    {
        if (empty($this->path)) {
            return false;
        }

        unlink($this->path);
        return true;
    }

    /**
     *
     */
    public function __serialize(): array
    {
        return [
            'path' => $this->path,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->path = $data['path'] ?? null;
    }
}
