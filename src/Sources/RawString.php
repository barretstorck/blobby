<?php

namespace BarretStorck\Blobby\Sources;

use BarretStorck\Blobby\Blob;
use BarretStorck\Blobby\BlobSourceInterface;

class RawString implements BlobSourceInterface
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
        return Blob::make()
            ->write($this->value)
            ->rewind();
    }

    /**
     *
     */
    public function save(Blob $input): bool
    {
        $value = strval($input);
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
