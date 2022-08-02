<?php

declare(strict_types=1);

namespace Worldline\Payment\Observer;

use Magento\Customer\Model\Context;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Gateway\Config\Config as PaymentGatewayConfig;
use Magento\Quote\Api\Data\CartInterface;
use Worldline\Payment\CreditCard\Gateway\Config\Config as CcConfig;
use Worldline\Payment\HostedCheckout\Gateway\Config\Config as HcConfig;
use Worldline\Payment\HostedCheckout\UI\ConfigProvider as HcConfigProvider;
use Worldline\Payment\CreditCard\UI\ConfigProvider as CcConfigProvider;

class PaymentMethodIsActive implements ObserverInterface
{
    /**
     * @var CcConfig
     */
    private $ccConfig;

    /**
     * @var HcConfig
     */
    private $hcConfig;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @param CcConfig $ccConfig
     * @param HcConfig $hcConfig
     * @param HttpContext $httpContext
     */
    public function __construct(
        CcConfig $ccConfig,
        HcConfig $hcConfig,
        HttpContext $httpContext
    ) {
        $this->ccConfig = $ccConfig;
        $this->hcConfig = $hcConfig;
        $this->httpContext = $httpContext;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Payment\Model\Method\Adapter $methodInstance */
        $methodInstance = $observer->getMethodInstance();
        $quote = $observer->getQuote();

        if ($methodInstance === null
            || $quote === null
        ) {
            return;
        }

        $code = $methodInstance->getCode();
        switch ($code) {
            case CcConfigProvider::CODE:
            case CcConfigProvider::CC_VAULT_CODE:
                $config = $this->ccConfig;
                break;
            case HcConfigProvider::HC_CODE:
            case HcConfigProvider::HC_VAULT_CODE:
                $config = $this->hcConfig;
                break;
            default:
                return;
        }

        $observer->getResult()->setIsAvailable(
            $this->checkIsAvailable($config, $quote)
        );
    }

    /**
     * @param PaymentGatewayConfig $config
     * @param CartInterface $quote
     * @return bool
     */
    public function checkIsAvailable(PaymentGatewayConfig $config, CartInterface $quote): bool
    {
        if (!$this->customerGroupValidation($config, $quote)) {
            return false;
        }

        return true;
    }

    /**
     * @param PaymentGatewayConfig $config
     * @param CartInterface $quote
     * @return bool
     */
    private function customerGroupValidation(PaymentGatewayConfig $config, CartInterface $quote): bool
    {
        $isValid = true;
        if ((int) $config->getValue('allow_specific_customer_group') === 1) {
            $availableCustomerGroups = array_map('intval', explode(
                ',',
                $config->getValue('customer_group')
            ));
            $currentCustomerGroup = $this->getCustomerGroup($quote);
            if (!in_array($currentCustomerGroup, $availableCustomerGroups)
                && !in_array(32000, $availableCustomerGroups) // ALL GROUPS
            ) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    /**
     * @param CartInterface $quote
     * @return int|null
     */
    private function getCustomerGroup(CartInterface $quote): ?int
    {
        /** @var Customer $customer */
        $customer = $quote->getCustomer();
        return (int) $customer->getGroupId() ?: $this->httpContext->getValue(Context::CONTEXT_GROUP);
    }
}
