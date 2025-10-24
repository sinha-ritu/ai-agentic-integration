<?php
/**
 * Copyright © 2025 Ritu Sinha
 *
 * This source code is licensed under the MIT license
 * that is bundled with this package in the file LICENSE.
 *
 * You are free to use, modify, and distribute this software
 * in accordance with the terms of the MIT License.
 */

declare(strict_types=1);

namespace SinhaR\AiAgenticIntegration\Cron;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use SinhaR\AiAgenticIntegration\Api\ACPExporterInterface;
use Psr\Log\LoggerInterface;

class SyncUpdatedProducts
{
    public function __construct(
        private readonly CollectionFactory $productCollectionFactory,
        private readonly ACPExporterInterface $exporter,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): void
    {
        $this->logger->info('SinhaR AI Agentic Integration: Starting product sync cron job.');

        $products = $this->productCollectionFactory->create();
        $products->addAttributeToSelect([
            'name',
            'description',
            'price',
            'image',
            'sku'
        ]);

        $twoHoursAgo = new \DateTime();
        $twoHoursAgo->sub(new \DateInterval('PT2H'));
        $products->addAttributeToFilter('updated_at', ['from' => $twoHoursAgo->format('Y-m-d H:i:s')]);

        if ($products->getSize() === 0) {
            $this->logger->info('SinhaR AI Agentic Integration: No products to sync.');
            return;
        }

        $this->logger->info(sprintf('SinhaR AI Agentic Integration: Found %d products to sync.', $products->getSize()));

        $data = [];
        foreach ($products as $product) {
            $data[] = $this->exporter->formatProductData($product);
        }

        try {
            $this->exporter->connectAndSendToACP($data);
            $this->logger->info('SinhaR AI Agentic Integration: Product sync cron job finished successfully.');
        } catch (\Exception $e) {
            $this->logger->error('SinhaR AI Agentic Integration: Exception during product sync: ' . $e->getMessage());
        }
    }
}
