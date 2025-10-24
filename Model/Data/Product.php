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

namespace SinhaR\AiAgenticIntegration\Model\Data;

use Magento\Catalog\Model\AbstractModel;
use SinhaR\AiAgenticIntegration\Api\Data\ProductInterface;

class Product extends AbstractModel implements ProductInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSku(): string
    {
        return $this->_getData(self::SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function setSku($sku): ProductInterface
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->_getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name): ProductInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice(): float
    {
        return $this->_getData(self::PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice($price): ProductInterface
    {
        return $this->setData(self::PRICE, $price);
    }

    public function getUrl(): string
    {
        return $this->_getData(self::URL);
    }

    public function setUrl($urlKey): ProductInterface
    {
        return $this->setData(self::URL, $urlKey);
    }
}
