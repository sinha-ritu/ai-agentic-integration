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

namespace SinhaR\AiAgenticIntegration\Api\Data;

use Magento\Catalog\Api\Data\ProductInterface as BaseProductInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;

interface ProductInterface extends CustomAttributesDataInterface
{
    public const SKU = BaseProductInterface::SKU;
    public const NAME = BaseProductInterface::NAME;
    public const PRICE = BaseProductInterface::PRICE;
    public const URL = 'url';

    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku): ProductInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name): ProductInterface;

    /**
     * @return float
     */
    public function getPrice(): float;

    /**
     * @param float $price
     * @return $this
     */
    public function setPrice($price): ProductInterface;


    /**
     * @return string
     */
    public function getUrl(): string;

    /**
     * @param string $urlKey
     * @return $this
     */
    public function setUrl($urlKey): ProductInterface;
}
