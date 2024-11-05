<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    private EntityManagerInterface $entityManager;

    private ProductRepository $productRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
    }

    public function saveProducts(array $productsData): void
    {
        $skus = array_column($productsData, 'sku');

        $existingProducts = $this->productRepository->findBy(['sku' => $skus]);

        $productsBySku = [];
        foreach ($existingProducts as $product) {
            $productsBySku[$product->getSku()] = $product;
        }

        foreach ($productsData as $data) {
            $sku = $data['sku'];

            if (isset($productsBySku[$sku])) {
                $product = $productsBySku[$sku];
                $product->setTitle($data['title']);
                $product->setPrice($data['price']);
                $product->setImageUrl($data['image_url']);
                $product->setProductUrl($data['product_url']);
            } else {
                $product = new Product();
                $product->setSku($sku);
                $product->setTitle($data['title']);
                $product->setPrice($data['price']);
                $product->setImageUrl($data['image_url']);
                $product->setProductUrl($data['product_url']);
                $this->entityManager->persist($product);
            }
        }
        $this->entityManager->flush();
    }
}
