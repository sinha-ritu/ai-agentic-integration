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

namespace SinhaR\AiAgenticIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use SinhaR\AiAgenticIntegration\Api\ACPExporterInterface;

class ProductSaveAfter implements ObserverInterface
{
    public function __construct(
        private readonly ACPExporterInterface $ACPExporter,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function execute(Observer $observer): void
    {
        $product = $observer->getEvent()->getProduct();
        // Map product to ACP schema
        $data = $this->ACPExporter->formatProductData($product);

        try {
            $message = $this->ACPExporter->connectAndSendToACP([$data]);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw $e;
        }

        $this->logger->debug($message ?? 'No message returned from ACPExporter');
    }
}

