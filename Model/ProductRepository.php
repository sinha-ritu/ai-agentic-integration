<?php
/**
 * Copyright © SinhaR Group. All rights reserved.
 * See LICENSE_SINHAR.txt for license details.
 */
declare(strict_types=1);

namespace SinhaR\AiAgenticIntegration\Model;

use Psr\Log\LoggerInterface;
use SinhaR\AiAgenticIntegration\Api\ProductRepositoryInterface;
use SinhaR\AiAgenticIntegration\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface as MagentoProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly MagentoProductRepositoryInterface $productRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly ProductInterfaceFactory $productInterfaceFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getList(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder->setPageSize(10)->create();
        $searchResults = $this->productRepository->getList($searchCriteria);
        $products = $searchResults->getItems();
        $productData = [];

        foreach ($products as $product) {
            $productDataObject = $this->productInterfaceFactory->create();
            $productDataObject->setSku($product->getSku());
            $productDataObject->setName($product->getName());
            $productDataObject->setPrice($product->getPrice());
            $productDataObject->setUrl($product->getProductUrl());

            $productData[] = $productDataObject;
        }

        return $productData;
    }
}
