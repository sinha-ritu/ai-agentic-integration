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

use SinhaR\AiAgenticIntegration\Api\Data\ProductInterface;

/**
 * @api
 */
interface ProductRepositoryInterface
{
    /**
     * Get a list of products.
     *
     * @return ProductInterface[]
     */
    public function getList(): array;
}
