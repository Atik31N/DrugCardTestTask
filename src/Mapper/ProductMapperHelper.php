<?php

namespace App\Mapper;

class ProductMapperHelper
{
    public function priceFormat(string $price): float
    {
        $floatPrice = preg_replace('/[^\d.]/', '', trim($price));
        return (float)$floatPrice;
    }

    public function productUrlFormat(string $productUrl): string
    {
        return trim($productUrl);
    }

    public function skuFormat(string $sku): string
    {
        return trim($sku);
    }

    public function imgUrlFormat(string $imageUrl): string
    {
        return trim($imageUrl);
    }

    public function titleFormat(string $title): string
    {
        return trim($title);
    }
}
