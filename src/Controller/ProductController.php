<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    #[Route('/api/products', name: 'products', methods: ['GET'])]
    public function getProducts(): JsonResponse
    {
        try {
            $response = [];
            $products = $this->productRepository->findAll();

            $response['count'] = count($products);

            foreach ($products as $product) {
                $response['products'][] = [
                    'title' => $product->getTitle(),
                    'price' => $product->getPrice(),
                    'image_url' => $product->getImageUrl(),
                    'product_url' => $product->getProductUrl(),
                ];
            }

            return new JsonResponse($response);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unable to fetch products'], 500);
        }
    }
}
