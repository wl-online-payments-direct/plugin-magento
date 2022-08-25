<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Config\ConnectionTest;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Filter\StripTags;
use Magento\Store\Model\ScopeInterface;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\Config\WorldlineConfig;

class FromAjaxRequest
{
    public const ENV_MODE = 'environment_mode';
    public const API_SECRET = ['api_secret', 'api_secret_prod'];
    public const API_ENDPOINT = ['testing_api_url', 'production_api_url'];
    public const API_KEY = ['api_key', 'api_key_prod'];
    public const MERCHANT_ID = ['merchant_id', 'merchant_id_prod'];

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ClientProvider
     */
    private $clientProvider;

    /**
     * @var StripTags
     */
    private $tagFilter;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var int
     */
    private $envMode = -1;

    public function __construct(
        ClientProvider $clientProvider,
        StripTags $tagFilter,
        WorldlineConfig $worldlineConfig,
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        $this->clientProvider = $clientProvider;
        $this->tagFilter = $tagFilter;
        $this->worldlineConfig = $worldlineConfig;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    public function test(): string
    {
        try {
            $this->initConfigParameters();
            $this->clientProvider->getClient()
                ->merchant($this->worldlineConfig->getMerchantId())
                ->services()
                ->testConnection();

            return '';
        } catch (\Exception $e) {
            return $this->tagFilter->filter($e->getMessage());
        }
    }

    private function initConfigParameters(): void
    {
        $this->worldlineConfig->setApiEndpoint($this->getEndpoint());
        $this->worldlineConfig->setMerchantId($this->getMerchantId());

        $apiKey = $this->getApiKey();
        $this->worldlineConfig->setApiKey(
            $this->isObscured($apiKey)
                ? $this->encryptor->decrypt($this->getFromConfig(self::API_KEY[$this->getEnvMode()]))
                : $apiKey
        );

        $apiSecret = $this->getApiSecret();
        $this->worldlineConfig->setApiSecret(
            $this->isObscured($apiSecret)
                ? $this->encryptor->decrypt($this->getFromConfig(self::API_SECRET[$this->getEnvMode()]))
                : $apiSecret
        );
    }

    private function getEnvMode(): int
    {
        if ($this->envMode === -1) {
            $this->envMode = (int) $this->request->getParam(self::ENV_MODE);
        }

        return $this->envMode;
    }

    private function getEndpoint(): string
    {
        return (string) $this->request->getParam(self::API_ENDPOINT[$this->getEnvMode()]);
    }

    private function getMerchantId(): string
    {
        return (string) $this->request->getParam(self::MERCHANT_ID[$this->getEnvMode()]);
    }

    private function getApiKey(): string
    {
        return trim((string) $this->request->getParam(self::API_KEY[$this->getEnvMode()]));
    }

    private function getApiSecret(): string
    {
        return trim((string) $this->request->getParam(self::API_SECRET[$this->getEnvMode()]));
    }

    private function isObscured(string $value): bool
    {
        return (bool) preg_match('/^[\*]+$/', $value);
    }

    private function getFromConfig(string $key): string
    {
        return (string) $this->scopeConfig->getValue("worldline_connection/connection/$key", ...$this->getScope());
    }

    private function getScope(): array
    {
        return ($this->request->getParam(ScopeInterface::SCOPE_WEBSITE) !== null)
            ? [ScopeInterface::SCOPE_WEBSITE, (int) $this->request->getParam(ScopeInterface::SCOPE_WEBSITE)]
            : [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null];
    }
}
