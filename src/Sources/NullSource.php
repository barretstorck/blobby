<?php

namespace BarretStorck\Blobby\Sources;

use BarretStorck\Blobby\Blob;
use BarretStorck\Blobby\ReadableBlobSourceInterface;
use BarretStorck\Blobby\WriteableBlobSourceInterface;
use BarretStorck\Blobby\DeleteableBlobSourceInterface;

class NullSource implements ReadableBlobSourceInterface, WriteableBlobSourceInterface, DeleteableBlobSourceInterface
{
    /**
     *
     */
    public function open(): Blob
    {
        return new Blob();
    }

    /**
     *
     */
    public function save(Blob $input): bool
    {
        return true;
    }

    /**
     *
     */
    public function delete(): bool
    {
        return true;
    }

    /**
     *
     */
    public function __serialize(): array
    {
        return [];
    }

    /**
     *
     */
    public function __unserialize(array $data): void
    {
        return;
    }
}
