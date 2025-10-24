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

namespace SinhaR\AiAgenticIntegration\Plugin;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Webapi\Rest\Request;
use SinhaR\AiAgenticIntegration\Api\ACPExporterInterface;

class AuthValidator
{
    public function __construct(
        private readonly Request $request,
        private readonly ACPExporterInterface $acpExporter,
    ) {
    }

    public function beforeDispatch(FrontControllerInterface $subject, RequestInterface $request): array
    {
        $path = $request->getPathInfo();
        // Only protect AI-Agent endpoints
        if (str_contains($path, '/rest') && strpos($path, '/V1/aiagent/') !== false) {
            $authHeader = $this->request->getHeader('Authorization');
            $expectedKey = $this->acpExporter->fetchApiKey() ?? (getenv('CHATGPT_API_KEY')?? null);

            if (!$authHeader || !preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
                throw new AuthorizationException(__('Missing Authorization header'));
            }

            $providedKey = trim($matches[1]);
            if ($providedKey !== $expectedKey) {
                throw new AuthorizationException(__('Invalid API key'));
            }
        }
        return [$request];
    }

}
