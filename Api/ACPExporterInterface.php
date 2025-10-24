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

namespace SinhaR\AiAgenticIntegration\Api;

use Magento\Catalog\Api\Data\ProductInterface;

interface ACPExporterInterface
{
    /**
     * Push product data to ACP endpoint.
     *
     * @return string
     */
    public function push();

    /**
     * @param ProductInterface $product
     * @return array
     */
    public function formatProductData(ProductInterface $product): array;

    /**
     * @return string
     */
    public function fetchApiKey(): string;

    /**
     * @param array $data
     * @return string
     */
    public function connectAndSendToACP(array $data): string;
}

