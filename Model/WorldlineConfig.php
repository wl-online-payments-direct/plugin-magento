<?php

declare(strict_types=1);

namespace Worldline\Payment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class WorldlineConfig
{
    public const XML_PATH_MODE = 'worldline_connection/connection/environment_mode';
    public const XML_PATH_MERCHANT_ID = 'worldline_connection/connection/merchant_id';
    public const XML_PATH_API_KEY = 'worldline_connection/connection/api_key';
    public const XML_PATH_API_SECRET = 'worldline_connection/connection/api_secret';
    public const XML_PATH_API_TEST_ENDPOINT = 'worldline_connection/connection/testing_api_url';
    public const XML_PATH_API_PRODUCTION_ENDPOINT = 'worldline_connection/connection/production_api_url';
    public const XML_PATH_LOGGING_LIFETIME = 'worldline_payment/debug/logging_lifetime';

    /**
     * @var string[]
     */
    private $ccTypesMapper = [
        1 => 'VI',
        2 => 'AE',
        3 => 'MC',
        117 => 'MI',
        125 => 'JCB',
        130 => 'CB',
        132 => 'DN',
    ];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var string | null
     */
    private $apiKey = null;

    /**
     * @var string | null
     */
    private $apiSecret = null;

    /**
     * @var string | null
     */
    private $merchantId = null;

    /**
     * @var string | null
     */
    private $apiEndpoint = null;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(ScopeConfigInterface $scopeConfig, EncryptorInterface $encryptor)
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @param int|null $scopeCode
     * @return bool
     */
    public function isProductionMode(?int $scopeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_MODE,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * @param int|null $scopeCode
     * @return string
     */
    public function getMerchantId(?int $scopeCode = null): string
    {
        if ($this->merchantId) {
            return $this->merchantId;
        }

        $this->merchantId = (string)$this->scopeConfig->getValue(
            self::XML_PATH_MERCHANT_ID,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId(string $merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @param int|null $scopeCode
     * @return string
     */
    public function getApiKey(?int $scopeCode = null): string
    {
        if ($this->apiKey) {
            return $this->apiKey;
        }

        $this->apiKey = $this->encryptor->decrypt(
            $this->scopeConfig->getValue(self::XML_PATH_API_KEY, ScopeInterface::SCOPE_STORE, $scopeCode)
        );
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param int|null $scopeCode
     * @return string
     */
    public function getApiSecret(?int $scopeCode = null):string
    {
        if ($this->apiSecret) {
            return $this->apiSecret;
        }

        $this->apiSecret = $this->encryptor->decrypt(
            $this->scopeConfig->getValue(self::XML_PATH_API_SECRET, ScopeInterface::SCOPE_STORE, $scopeCode)
        );
        return $this->apiSecret;
    }

    /**
     * @param string $apiSecret
     */
    public function setApiSecret(string $apiSecret)
    {
        $this->apiSecret = $apiSecret;
    }

    /**
     * @param int|null $scopeCode
     * @return string
     */
    public function getApiEndpoint(?int $scopeCode = null): string
    {
        if ($this->apiEndpoint) {
            return $this->apiEndpoint;
        }

        $xmlPath = self::XML_PATH_API_TEST_ENDPOINT;
        if ($this->isProductionMode($scopeCode)) {
            $xmlPath = self::XML_PATH_API_PRODUCTION_ENDPOINT;
        }

        $this->apiEndpoint = (string)$this->scopeConfig->getValue($xmlPath, ScopeInterface::SCOPE_STORE, $scopeCode);

        return $this->apiEndpoint;
    }

    /**
     * @param string $apiEndpoint
     */
    public function setApiEndpoint(string $apiEndpoint)
    {
        $this->apiEndpoint = $apiEndpoint;
    }

    /**
     * @param int $type
     * @return string|null
     */
    public function mapCcType(int $type): ?string
    {
        return $this->ccTypesMapper[$type] ?? null;
    }

    /**
     * @param int|null $scopeCode
     * @return string|null
     */
    public function getLoggingLifetime(?int $scopeCode = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOGGING_LIFETIME,
            ScopeInterface:: SCOPE_STORE,
            $scopeCode
        );
    }
}
