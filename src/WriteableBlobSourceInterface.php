<?php

namespace BarretStorck\Blobby;

use BarretStorck\Blobby\Blob;

interface WriteableBlobSourceInterface
{
    public function save(Blob $input): bool;

    public function __serialize();

    public function __unserialize(array $data): void;
}
