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

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use SinhaR\AiAgenticIntegration\Api\ACPExporterInterface;
use Magento\Framework\HTTP\Client\Curl;

class ACPExporter implements ACPExporterInterface
{
    private const XML_ACP_ENDPOINT_PATH = 'sinhar_aiagentic_integration/sinhar_aiagent/acp_settings/acp_endpoint';
    private const XML_GPT_API_KEY_PATH = 'sinhar_aiagentic_integration/sinhar_aiagent/acp_settings/ai_agent_api_key';

    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly Curl $curl,
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    private function fetchEndpoint(): string
    {
        // Fetch the endpoint from configuration or environment
        // Get ACP endpoint from admin config
        $endpoint = $this->scopeConfig->getValue(
            self::XML_ACP_ENDPOINT_PATH,
            ScopeInterface::SCOPE_STORE
        );

        if (!$endpoint) {
            throw new \Exception("ACP endpoint is not configured.");
        }

        return $endpoint;
    }

    public function fetchApiKey(): string
    {
        // Fetch the endpoint from configuration or environment
        // Get ACP endpoint from admin config
        $apiKey = $this->scopeConfig->getValue(
            self::XML_GPT_API_KEY_PATH,
            ScopeInterface::SCOPE_STORE
        );

        if (!$apiKey) {
            throw new \Exception("GPT API Key is not configured.");
        }

        return $apiKey;
    }

    public function push()
    {
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect([
                'name',
                'price',
                'sku',
                'url_key',
                'image',
                'description',
                'status'
            ]);

        $products = [];
        foreach ($collection as $product) {
            $products[] = $this->formatProductData($product);
        }

        try {
            return $this->connectAndSendToACP($products);
        } catch (\Exception $e) {
            return "Error pushing data: " . $e->getMessage();
        }
    }

    public function formatProductData(ProductInterface $product): array
    {
        $baseUrl = rtrim($this->scopeConfig->getValue('web/unsecure/base_url', ScopeInterface::SCOPE_STORE), '/');
        $data = [
            "@context" => "https://schema.org",
            "@type" => "Product",
            "sku" => $product->getSku(),
            "name" => $product->getName(),
            "description" => strip_tags($product->getDescription()),
            "image" => $product->getImage() ? $product->getMediaConfig()->getMediaUrl($product->getImage()) : null,
            "offers" => [
                "@type" => "Offer",
                "price" => $product->getPrice(),
                "priceCurrency" => "EUR",
                "availability" => $product->isAvailable() ? "InStock" : "OutOfStock",
                "url" => $product->getProductUrl()
            ],
            "potentialAction" => [
                "@type" =>  "BuyAction",
                "target" => [
                    "@type" => "EntryPoint",
                    "urlTemplate" => $baseUrl . "rest/V1/aiagent/createSession?sku={sku}&qty={quantity}",
                    "httpMethod" => "POST",
                    "encodingType" => "application/json"
                ],
                "query-input" => [
                    "required name=sku",
                    "required name=qty"
                ]
            ]
        ];

        return $data;
    }

    public function connectAndSendToACP(array $data): string
    {
        $payload = json_encode($data, JSON_PRETTY_PRINT);
        $endpoint = $this->fetchEndpoint();
        try {
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->post($endpoint, $payload);

            if ($this->curl->getStatus() == 200) {
                return "Successfully pushed data to ACP.";
            }

            return "Failed to push data. HTTP Code: " . $this->curl->getStatus();
        } catch (\Exception $e) {
            return "Error pushing data: " . $e->getMessage();
        }
    }
}
