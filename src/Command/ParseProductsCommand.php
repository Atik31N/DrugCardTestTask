<?php

namespace App\Command;

use App\Mapper\ProductMapperHelper;
use App\Message\SaveToCsvMessage;
use App\Service\ProductService;
use DOMDocument;
use DOMXPath;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:parse-products',
    description: 'Product parser',
)]
class ParseProductsCommand extends Command
{
    private HttpClientInterface $client;
    private MessageBusInterface $messageBus;
    private ProductService $productService;

    private ProductMapperHelper $productMapperHelper;

    public function __construct(
        HttpClientInterface    $client,
        MessageBusInterface    $messageBus,
        ProductService         $productService,
        ProductMapperHelper    $productMapperHelper
    )
    {
        parent::__construct();
        $this->client = $client;
        $this->messageBus = $messageBus;
        $this->productService = $productService;
        $this->productMapperHelper = $productMapperHelper;
    }

    protected function configure(): void
    {
        $this->setDescription('Product parser');
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $urls = [
            'https://topcompany.com.ua/ryukzaki-i-sumki/shkolnyye-ryukzaki',
            'https://topcompany.com.ua/ryukzaki-i-sumki/shkolnyye-ryukzaki/?page=2',
            'https://topcompany.com.ua/ryukzaki-i-sumki/shkolnyye-ryukzaki/?page=3'
        ];

        $products = [];
        $iterationID = time();

        foreach ($urls as $url) {
            $io->info("Loading page: $url");
            $response = $this->client->request('GET', $url);

            if ($response->getStatusCode() !== 200) {
                $io->error("Page $url was not found");
                continue;
            }

            $html = $response->getContent();

            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            $titles = $xpath->query('//div[@class="product_box_name"]/a');
            $prices = $xpath->query('//div[@class="caption_price_regular"]');
            $images = $xpath->query('//div[@class="product_box_image"]/a/img');
            $links = $xpath->query('//div[@class="product_box_name"]/a/@href');
            $skus = $xpath->query('//div[@class="model-info"]/text()[2]');

            foreach ($titles as $i => $titleNode) {
                $productData = [
                    'title' => $this->productMapperHelper->titleFormat($titleNode->nodeValue),
                    'price' => $this->productMapperHelper->priceFormat($prices->item($i)->nodeValue ?? '0'),
                    'image_url' => $this->productMapperHelper->imgUrlFormat($images->item($i)->getAttribute('src') ?? ''),
                    'product_url' => $this->productMapperHelper->productUrlFormat($links->item($i)->nodeValue ?? ''),
                    'sku' => $this->productMapperHelper->skuFormat($skus->item($i)->nodeValue ?? ''),
                ];
                $products[] = $productData;

                $this->messageBus->dispatch(new SaveToCsvMessage($productData, 'products_'. $iterationID));
            }
            $this->productService->saveProducts($products);
        }

        $io->success("Products found: " . count($products));
        return Command::SUCCESS;
    }
}
