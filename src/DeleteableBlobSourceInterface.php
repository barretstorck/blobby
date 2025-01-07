<?php

namespace BarretStorck\Blobby;

interface DeleteableBlobSourceInterface
{
    public function delete(): bool;

    public function __serialize();

    public function __unserialize(array $data): void;
}
