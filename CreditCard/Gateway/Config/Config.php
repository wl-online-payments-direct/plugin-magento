<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Gateway\Config;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Config\Config as PaymentGatewayConfig;
use Worldline\Payment\CreditCard\UI\ConfigProvider;

class Config extends PaymentGatewayConfig
{
    public const AUTHORIZATION_MODE = 'authorization_mode';
    public const AUTHORIZATION_MODE_FINAL = 'FINAL_AUTHORIZATION';
    public const AUTHORIZATION_MODE_PRE = 'PRE_AUTHORIZATION';
    public const AUTHORIZATION_MODE_SALE = 'SALE';
    public const CC_TYPES = 'cc_types';
    public const TEMPLATE_ID = 'template_id';
    public const SKIP_3D = 'skip_3d';
    public const PWA_ROUTE = 'pwa_route';
    public const KEY_ACTIVE = 'active';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var State
     */
    private $appState;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder,
        State $appState,
        $pathPattern = PaymentGatewayConfig::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, ConfigProvider::CODE, $pathPattern);
        $this->urlBuilder = $urlBuilder;
        $this->appState = $appState;
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getTemplateId($storeId = null): string
    {
        return (string) $this->getValue(self::TEMPLATE_ID, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getAuthorizationMode($storeId = null): string
    {
        $authorizationMode = (string) $this->getValue(self::AUTHORIZATION_MODE, $storeId);
        switch ($authorizationMode) {
            case 'pre':
                return self::AUTHORIZATION_MODE_PRE;
            default:
                return self::AUTHORIZATION_MODE_FINAL;
        }
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function hasSkipAuthentication($storeId = null): bool
    {
        return (bool) $this->getValue(self::SKIP_3D, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     * @throws LocalizedException
     */
    public function getReturnUrl(?int $storeId = null): string
    {
        $pwaRoute = (string) $this->getValue(self::PWA_ROUTE, $storeId);
        if ($pwaRoute && $this->appState->getAreaCode() === Area::AREA_GRAPHQL) {
            return $pwaRoute;
        }

        return $this->urlBuilder->getUrl('worldline/payment/result');
    }

    /**
     * Gets Payment configuration status
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive(?int $storeId = null): bool
    {
        return (bool) $this->getValue(self::KEY_ACTIVE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getCcTypes(?int $storeId = null): string
    {
        return (string) $this->getValue(self::CC_TYPES, $storeId);
    }
}
