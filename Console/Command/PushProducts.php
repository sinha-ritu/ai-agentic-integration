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

namespace SinhaR\AiAgenticIntegration\Console\Command;

use Magento\Framework\App\State;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SinhaR\AiAgenticIntegration\Api\ACPExporterInterface;

class PushProducts extends Command
{
    public function __construct(
        private readonly State $state,
        private readonly CollectionFactory $productCollectionFactory,
        private readonly ACPExporterInterface $exporter,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('sinhar:aiagent:push')
            ->setDescription('Push all products to ACP');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode('adminhtml');
        } catch (\Exception $e) {
            throw $e;
        }

        $products = $this->productCollectionFactory->create();
        $products->addAttributeToSelect([
            'name',
            'description',
            'price',
            'image',
            'sku'
        ]);

        $data = [];
        foreach ($products as $product) {
            $data[] = $this->exporter->formatProductData($product);
        }

        try {
            $this->exporter->connectAndSendToACP($data);
        } catch (\Exception $e) {
            $output->writeln("<error>Exception: " . $e->getMessage() . "</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
