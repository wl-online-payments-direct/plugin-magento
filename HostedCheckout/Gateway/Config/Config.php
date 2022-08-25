<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Gateway\Config;

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
    public const SKIP_3D = 'skip_3d';

    public const DIRECT_DEBIT_RECURRENCE_TYPE = 'direct_debit_recurrence_type';
    public const DIRECT_DEBIT_SIGNATURE_TYPE = 'direct_debit_signature_type';

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
        $methodCode = null,
        $pathPattern = PaymentGatewayConfig::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->urlBuilder = $urlBuilder;
        $this->appState = $appState;
    }

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

        return $this->urlBuilder->getUrl('worldline/HostedCheckout/ReturnUrl');
    }

    public function isActive(?int $storeId = null): bool
    {
        return (bool) $this->getValue(self::KEY_ACTIVE, $storeId);
    }

    public function isCartLines(?int $storeId = null): bool
    {
        return (bool) $this->getValue(self::KEY_CART_LINES, $storeId);
    }

    public function hasSkipAuthentication(?int $storeId = null): bool
    {
        return (bool) $this->getValue(self::SKIP_3D, $storeId);
    }

    public function getDirectDebitRecurrenceType(?int $storeId = null): string
    {
        return (string) $this->getValue(self::DIRECT_DEBIT_RECURRENCE_TYPE, $storeId);
    }

    public function getDirectDebitSignatureType(?int $storeId = null): string
    {
        return (string) $this->getValue(self::DIRECT_DEBIT_SIGNATURE_TYPE, $storeId);
    }
}
