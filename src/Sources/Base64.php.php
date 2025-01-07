<?php

namespace BarretStorck\Blobby\Sources;

use BarretStorck\Blobby\Blob;
use BarretStorck\Blobby\BlobSourceInterface;

class Base64 implements BlobSourceInterface
{
    protected string $value = '';

    /**
     *
     */
    public function __construct(string $value = '')
    {
        $this->setValue($value);
    }

    /**
     *
     */
    public function setValue(string $value = ''): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     *
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     *
     */
    public function open(): Blob
    {
        $value = base64_decode($this->value);
        return Blob::make()
            ->write($value)
            ->rewind();
    }

    /**
     *
     */
    public function save(Blob $input): bool
    {
        $value = base64_encode(strval($input));
        $this->setValue($value);
        return true;
    }

    /**
     *
     */
    public function delete(): bool
    {
        $this->setValue();
        return true;
    }

    /**
     *
     */
    public function __serialize(): array
    {
        return [
            'value' => $this->value,
        ];
    }

    /**
     *
     */
    public function __unserialize(array $data): void
    {
        $this->setValue($data['value'] ?? '');
    }
}
