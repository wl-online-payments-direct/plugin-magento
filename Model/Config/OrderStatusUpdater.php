<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class OrderStatusUpdater
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var string[]|null
     */
    private $data;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param string[]|null $data
     */
    public function __construct(ScopeConfigInterface $scopeConfig, EncryptorInterface $encryptor, array $data = [])
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isReceivingWebhooksAllowed(): bool
    {
        return (bool) $this->getValue('allow_receiving_webhooks');
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->encryptor->decrypt($this->getValue('key'));
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->encryptor->decrypt($this->getValue('secret_key'));
    }

    /**
     * @return int
     */
    public function getFallbackTimeout(): int
    {
        return (int) $this->getValue('fallback_timeout');
    }

    /**
     * @return int
     */
    public function getFallbackTimeoutLimit(): int
    {
        return (int) $this->getValue('fallback_timeout_limit');
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getWaitingTime(?int $storeId = null): int
    {
        return (int) $this->getValue('waiting_time', $storeId);
    }

    /**
     * @param string $configName
     * @param int|null $storeId
     * @return string
     */
    private function getValue(string $configName, ?int $storeId = null): string
    {
        $xmlConfigPath = $this->data[$configName] ?? '';
        if (!$xmlConfigPath) {
            return '';
        }

        return (string) $this->scopeConfig->getValue($xmlConfigPath, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
