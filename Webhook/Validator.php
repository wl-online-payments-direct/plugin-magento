<?php

declare(strict_types=1);

namespace Worldline\Payment\Webhook;

use OnlinePayments\Sdk\Webhooks\InMemorySecretKeyStoreFactory;
use OnlinePayments\Sdk\Webhooks\WebhooksHelperFactory;
use Worldline\Payment\Model\Config\OrderStatusUpdater as WebhookConfig;

class Validator
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

    /**
     * @param WebhookConfig $webhookConfig
     * @param InMemorySecretKeyStoreFactory $inMemorySecretKeyStoreFactory
     * @param WebhooksHelperFactory $webhooksHelperFactory
     */
    public function __construct(
        WebhookConfig $webhookConfig,
        InMemorySecretKeyStoreFactory $inMemorySecretKeyStoreFactory,
        WebhooksHelperFactory $webhooksHelperFactory
    ) {
        $this->webhookConfig = $webhookConfig;
        $this->inMemorySecretKeyStoreFactory = $inMemorySecretKeyStoreFactory;
        $this->webhooksHelperFactory = $webhooksHelperFactory;
    }

    /**
     * @param string $body
     * @param string $signature
     * @param string $keyId
     *
     * @return bool
     */
    public function isAuthorized(string $body, string $signature, string $keyId): bool
    {
        if (!$this->webhookConfig->isReceivingWebhooksAllowed()) {
            return false;
        }

        $secretKeyStore = $this->inMemorySecretKeyStoreFactory->create([
            'secretKeys' => [
                $this->webhookConfig->getKey() => $this->webhookConfig->getSecretKey()
            ]
        ]);
        $helper = $this->webhooksHelperFactory->create(['secretKeyStore' => $secretKeyStore]);

        try {
            $helper->unmarshal($body, [
                'X-GCS-Signature' => $signature,
                'X-GCS-KeyId' => $keyId
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
