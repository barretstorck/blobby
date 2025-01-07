<?php

namespace BarretStorck\Blobby;

use BarretStorck\Blobby\Blob;

interface ReadableBlobSourceInterface
{
    public function open(): Blob;

    public function __serialize();

    public function __unserialize(array $data): void;
}
