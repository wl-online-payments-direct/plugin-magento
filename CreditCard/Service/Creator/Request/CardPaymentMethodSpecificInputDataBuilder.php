<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Service\Creator\Request;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInput;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInputFactory;
use OnlinePayments\Sdk\Domain\RedirectionDataFactory;
use OnlinePayments\Sdk\Domain\ThreeDSecure;
use OnlinePayments\Sdk\Domain\ThreeDSecureFactory;
use Worldline\Payment\CreditCard\Gateway\Config\Config;

class CardPaymentMethodSpecificInputDataBuilder
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CardPaymentMethodSpecificInputFactory
     */
    private $cardPaymentMethodSpecificInputFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ThreeDSecureFactory
     */
    private $threeDSecureFactory;

    /**
     * @var RedirectionDataFactory
     */
    private $redirectionDataFactory;

    /**
     * @var string|null
     */
    private $returnUrl;

    public function __construct(
        Config $config,
        CardPaymentMethodSpecificInputFactory $cardPaymentMethodSpecificInputFactory,
        StoreManagerInterface $storeManager,
        ThreeDSecureFactory $threeDSecureFactory,
        RedirectionDataFactory $redirectionDataFactory
    ) {
        $this->config = $config;
        $this->cardPaymentMethodSpecificInputFactory = $cardPaymentMethodSpecificInputFactory;
        $this->storeManager = $storeManager;
        $this->threeDSecureFactory = $threeDSecureFactory;
        $this->redirectionDataFactory = $redirectionDataFactory;
    }

    public function build(CartInterface $quote): CardPaymentMethodSpecificInput
    {
        $cardPaymentMethodSpecificInput = $this->cardPaymentMethodSpecificInputFactory->create();

        $cardPaymentMethodSpecificInput->setAuthorizationMode($this->getAuthorizationMode());
        $cardPaymentMethodSpecificInput->setReturnUrl($this->getReturnUrl());
        $cardPaymentMethodSpecificInput->setThreeDSecure($this->getTreeDSecure());
        $cardPaymentMethodSpecificInput->setToken($quote->getPayment()->getAdditionalInformation('token_id'));

        return $cardPaymentMethodSpecificInput;
    }

    private function getAuthorizationMode()
    {
        if ($this->config->getValue('payment_action') === 'authorize_capture') {
            return Config::AUTHORIZATION_MODE_SALE;
        }

        return $this->config->getAuthorizationMode();
    }

    private function getReturnUrl(): string
    {
        if (null === $this->returnUrl) {
            $storeId = (int) $this->storeManager->getStore()->getId();
            $this->returnUrl = $this->config->getReturnUrl($storeId);
        }

        return $this->returnUrl;
    }

    private function getTreeDSecure(): ThreeDSecure
    {
        $threeDSecure = $this->threeDSecureFactory->create();
        $threeDSecure->setSkipAuthentication($this->config->hasSkipAuthentication());
        $redirectionData = $this->redirectionDataFactory->create();
        $redirectionData->setReturnUrl($this->getReturnUrl());
        $threeDSecure->setRedirectionData($redirectionData);

        return $threeDSecure;
    }
}
