<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Config\HostedCheckout;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Config\Config as PaymentGatewayConfig;

class Config extends PaymentGatewayConfig
{
    public const AUTHORIZATION_MODE = 'authorization_mode';
    public const PAYMENT_ACTION = 'payment_action';
    public const AUTHORIZATION_MODE_FINAL = 'FINAL_AUTHORIZATION';
    public const AUTHORIZATION_MODE_PRE = 'PRE_AUTHORIZATION';
    public const AUTHORIZATION_MODE_SALE = 'SALE';
    public const AUTHORIZE_CAPTURE = 'authorize_capture';
    public const TEMPLATE_ID = 'template_id';
    public const PWA_ROUTE = 'pwa_route';
    public const KEY_ACTIVE = 'active';
    public const KEY_CART_LINES = 'cart_lines';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var State
     */
    private $appState;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlBuilder
     * @param State $appState
     * @param $methodCode
     * @param $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder,
        State $appState,
        $methodCode = null,
        $pathPattern = PaymentGatewayConfig::DEFAULT_PATH_PATTERN
    ) {
        PaymentGatewayConfig::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->urlBuilder = $urlBuilder;
        $this->appState = $appState;
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getAuthorizationMode($storeId = null): string
    {
        if ($this->getValue(self::PAYMENT_ACTION, $storeId) === self::AUTHORIZE_CAPTURE) {
            return self::AUTHORIZATION_MODE_SALE;
        }

        $authorizationMode = (string) $this->getValue(self::AUTHORIZATION_MODE, $storeId);
        switch ($authorizationMode) {
            case 'pre':
                return self::AUTHORIZATION_MODE_PRE;
            default:
                return self::AUTHORIZATION_MODE_FINAL;
        }
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getTemplateId(?int $storeId = null): string
    {
        return (string) $this->getValue(self::TEMPLATE_ID, $storeId);
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

        return $this->urlBuilder->getUrl('worldline/payment/hosted');
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isActive(?int $storeId = null): bool
    {
        return (bool) $this->getValue(self::KEY_ACTIVE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isCartLines(?int $storeId = null): bool
    {
        return (bool) $this->getValue(self::KEY_CART_LINES, $storeId);
    }
}
