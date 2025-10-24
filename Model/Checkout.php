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

namespace SinhaR\AiAgenticIntegration\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;
use SinhaR\AiAgenticIntegration\Api\CheckoutInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * NOT USABLE FOR NOW. STILL THINKING THROUGH THE BEST APPROACH.
 */
class Checkout implements CheckoutInterface
{
    private const XML_STRIPE_SECRET_KEY_PATH = 'payment/aiagent_integration/stripe_secret_key';

    public function __construct(
        private readonly CartManagementInterface $cartManagement,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly UrlInterface $urlBuilder,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function create(): string
    {
        $cartId = $this->cartManagement->createEmptyCart();

        return (string) $cartId;
    }

    /**
     * {@inheritdoc}
     */
    public function update($cartId, $items): bool
    {
        $quote = $this->cartRepository->get($cartId);

        foreach ($items as $item) {
            $product = $this->productRepository->get($item['sku']);
            $quote->addProduct($product, (int)$item['qty']);
        }

        $this->cartRepository->save($quote);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function complete($cartId, $paymentToken): ?string
    {
        // IMPORTANT: Replace with your actual Stripe secret key.
        // It is strongly recommended to store this securely (e.g., in Magento's configuration).
        $stripeSecretKey = $this->scopeConfig->getValue(self::XML_STRIPE_SECRET_KEY_PATH);
        Stripe::setApiKey($stripeSecretKey);
        $quote = $this->cartRepository->get($cartId);
        $quote->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates();

        // This is a simplified example. A real implementation needs to set a shipping method.
        // For now, we'll just get the first available one.
        $shippingAddress = $quote->getShippingAddress();
        $shippingRates = $shippingAddress->getAllShippingRates();
        if (count($shippingRates) > 0) {
            $shippingAddress->setShippingMethod($shippingRates[0]->getCode());
        }

        $quote->setPaymentMethod('stripe_payments');
        $quote->getPayment()->importData(['method' => 'stripe_payments']);
        $this->cartRepository->save($quote);
        $grandTotal = $quote->getGrandTotal();

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $grandTotal * 100, // Amount in cents
                'currency' => $quote->getQuoteCurrencyCode(),
                'payment_method' => $paymentToken,
                'confirmation_method' => 'manual',
                'confirm' => true,
            ]);

            if ($paymentIntent->status == 'succeeded') {
                // Payment is successful, now place the order in Magento.
                $orderId = $this->cartManagement->placeOrder($quote->getId());
                return (string) $orderId;
            }

            throw new LocalizedException(__('Stripe payment failed with status: %1', $paymentIntent->status));
        } catch (ApiErrorException $e) {
            throw new LocalizedException(__('Stripe API Error: %1', $e->getMessage()));
        } catch (\Exception $e) {
            throw new LocalizedException(__('An error occurred while completing the checkout: %1', $e->getMessage()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cancel($cartId): bool
    {
        $quote = $this->cartRepository->get($cartId);
        $quote->setIsActive(false);
        $this->cartRepository->save($quote);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function createSession(string $sku, int $quantity): array
    {
        try {
            $cartId = $this->create();
            $quote = $this->cartRepository->get($cartId);
            $product = $this->productRepository->get($sku);
            $quote->addProduct($product, $quantity);
            $this->cartRepository->save($quote);
            $store = $quote->getStore();
            $currency = $store->getCurrentCurrencyCode();

            // Ensure description is never null
            $description = $product->getShortDescription() ?: $product->getName();

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'product_data' => [
                            'name' => $product->getName(),
                            'description' => $description,
                        ],
                        // Stripe expects amount in cents (integer)
                        'unit_amount' => (int)round($product->getPrice() * 100),
                    ],
                    'quantity' => max(1, $quantity), // avoid 0 quantity
                ]],
                'mode' => 'payment',
                'success_url' => $this->urlBuilder->getUrl('rest/V1/aiagent/checkout/' . $cartId . '/complete'),
                'cancel_url' => $this->urlBuilder->getUrl('rest/V1/aiagent/checkout/' . $cartId. '/cancel'),
            ]);

            return [
                'session_id' => $session->id,
                'checkout_url' => $session->url,
                'status' => 'pending',
                'currency' => strtolower($currency),
                'amount_total' => $session->amount_total ?? $product->getPrice() * $quantity,
            ];

        } catch (\Exception $e) {
            throw new LocalizedException(
                __("Failed to create checkout session: %1", $e->getMessage())
            );
        }
    }

    public function getSession($id): array
    {
        $session = StripeSession::retrieve($id);
        return [
            'session_id' => $session->id,
            'status' => $session->status,
            'order_number' => $session->metadata->order_number ?? null
        ];
    }

    public function handleWebhook(): array
    {
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, getenv('STRIPE_WEBHOOK_SECRET')
            );
        } catch(\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            // TODO: create Magento order from session
        }

        http_response_code(200);
        return ['status' => 'ok'];
    }
}
