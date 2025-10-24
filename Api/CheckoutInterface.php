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

/**
 * @api
 */
interface CheckoutInterface
{
    /**
     * Update a checkout.
     *
     * @param string $cartId
     * @param mixed $items
     * @return bool
     */
    public function update($cartId, $items): bool;

    /**
     * Complete a checkout.
     *
     * @param string $cartId
     * @param string $paymentToken
     * @return string|null
     */
    public function complete($cartId, $paymentToken): ?string;

    /**
     * Cancel a checkout.
     *
     * @param string $cartId
     * @return bool
     */
    public function cancel($cartId): bool;

    /**
     * @param string $sku
     * @param int $quantity
     * @return array
     */
    public function createSession(string $sku, int $quantity): array;

    /**
     * @param $id
     * @return array
     */
    public function getSession($id): array;

    /**
     * @return array
     */
    public function handleWebhook(): array;
}
