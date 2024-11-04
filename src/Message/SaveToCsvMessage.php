<?php

namespace App\Message;

class SaveToCsvMessage
{
    private array $product;
    private string $filename;

    public function __construct(array $product, string $filename)
    {
        $this->product = $product;
        $this->filename = $filename;
    }

    public function getProduct(): array
    {
        return $this->product;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
