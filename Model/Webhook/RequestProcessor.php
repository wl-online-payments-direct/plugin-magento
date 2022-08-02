<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Webhook;

use OnlinePayments\Sdk\Domain\WebhooksEvent;
use OnlinePayments\Sdk\Webhooks\InMemorySecretKeyStoreFactory;
use OnlinePayments\Sdk\Webhooks\SignatureValidationException;
use OnlinePayments\Sdk\Webhooks\WebhooksHelperFactory;
use Worldline\Payment\Model\Config\OrderStatusUpdater as WebhookConfig;

/**
 * @core
 */
class RequestProcessor
{
    /**
     * @var WebhookConfig
     */
    private $webhookConfig;

    /**
     * @var InMemorySecretKeyStoreFactory
     */
    private $inMemorySecretKeyStoreFactory;

    /**
     * @var WebhooksHelperFactory
     */
    private $webhooksHelperFactory;

    public function __construct(
        WebhookConfig $webhookConfig,
        InMemorySecretKeyStoreFactory $inMemorySecretKeyStoreFactory,
        WebhooksHelperFactory $webhooksHelperFactory
    ) {
        $this->webhookConfig = $webhookConfig;
        $this->inMemorySecretKeyStoreFactory = $inMemorySecretKeyStoreFactory;
        $this->webhooksHelperFactory = $webhooksHelperFactory;
    }

    public function getWebhookEvent(string $body, string $signature, string $keyId): ?WebhooksEvent
    {
        if (!$this->webhookConfig->isReceivingWebhooksAllowed()) {
            return null;
        }

        $secretKeyStore = $this->inMemorySecretKeyStoreFactory->create([
            'secretKeys' => [
                $this->webhookConfig->getKey() => $this->webhookConfig->getSecretKey()
            ]
        ]);
        $helper = $this->webhooksHelperFactory->create(['secretKeyStore' => $secretKeyStore]);

        try {
            return $helper->unmarshal($body, [
                'X-GCS-Signature' => $signature,
                'X-GCS-KeyId' => $keyId
            ]);
        } catch (SignatureValidationException $e) {
            return null;
        }
    }
}
