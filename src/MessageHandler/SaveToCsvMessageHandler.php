<?php

namespace App\MessageHandler;

use App\Message\SaveToCsvMessage;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use League\Csv\Writer;

#[AsMessageHandler(handles: SaveToCsvMessage::class)]
class SaveToCsvMessageHandler
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(SaveToCsvMessage $message): void
    {
        $product = $message->getProduct();
        $filePath = sprintf("var/data/%s.csv", $message->getFileName());

        if (!is_dir(dirname($filePath))) {
            if (!mkdir(dirname($filePath), 0775, true) && !is_dir(dirname($filePath))) {
                $this->logger->error(sprintf('Directory: %s was not created.', dirname($filePath)));
                throw new RuntimeException(sprintf('Directory: %s was not created.', dirname($filePath)));
            }
        }
        try {
            $csv = Writer::createFromPath($filePath, 'a+');

            $csv->insertOne([
                $product['title'],
                $product['price'],
                $product['image_url'],
                $product['product_url']
            ]);
        } catch (RuntimeException $e) {
            $this->logger->error("Error writing to CSV file: " . $e->getMessage());
            throw new RuntimeException("Error writing to CSV file: " . $e->getMessage());
        }
    }
}
