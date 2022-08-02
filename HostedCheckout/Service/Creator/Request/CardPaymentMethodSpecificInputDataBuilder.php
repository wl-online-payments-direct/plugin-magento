<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Service\Creator\Request;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInput;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInputFactory;
use OnlinePayments\Sdk\Domain\RedirectionDataFactory;
use OnlinePayments\Sdk\Domain\ThreeDSecureFactory;
use Worldline\Payment\HostedCheckout\Gateway\Config\Config;

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
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    /**
     * @var ThreeDSecureFactory
     */
    private $threeDSecureFactory;

    /**
     * @var RedirectionDataFactory
     */
    private $redirectionDataFactory;

    public function __construct(
        Config $config,
        CardPaymentMethodSpecificInputFactory $cardPaymentMethodSpecificInputFactory,
        PaymentTokenManagementInterface $paymentTokenManagement,
        ThreeDSecureFactory $threeDSecureFactory,
        RedirectionDataFactory $redirectionDataFactory
    ) {
        $this->config = $config;
        $this->cardPaymentMethodSpecificInputFactory = $cardPaymentMethodSpecificInputFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->threeDSecureFactory = $threeDSecureFactory;
        $this->redirectionDataFactory = $redirectionDataFactory;
    }

    public function build(CartInterface $quote): CardPaymentMethodSpecificInput
    {
        /** @var CardPaymentMethodSpecificInput $cardPaymentMethodSpecificInput */
        $cardPaymentMethodSpecificInput = $this->cardPaymentMethodSpecificInputFactory->create();
        $cardPaymentMethodSpecificInput->setAuthorizationMode($this->config->getAuthorizationMode());

        $cardPaymentMethodSpecificInput->setThreeDSecure($this->getThreeDSecure());

        if ($token = $this->getToken($quote)) {
            $cardPaymentMethodSpecificInput->setToken($token);
        }

        return $cardPaymentMethodSpecificInput;
    }

    private function getThreeDSecure()
    {
        $threeDSecure = $this->threeDSecureFactory->create();
        $threeDSecure->setSkipAuthentication($this->config->hasSkipAuthentication());
        $redirectionData = $this->redirectionDataFactory->create();
        $redirectionData->setReturnUrl($this->config->getReturnUrl());
        $threeDSecure->setRedirectionData($redirectionData);

        return $threeDSecure;
    }

    private function getToken(CartInterface $quote): ?string
    {
        $payment = $quote->getPayment();
        if (!$publicHash = $payment->getAdditionalInformation(PaymentTokenInterface::PUBLIC_HASH)) {
            return null;
        }

        $token = $this->paymentTokenManagement->getByPublicHash($publicHash, (int) $quote->getCustomerId());
        return $token instanceof PaymentTokenInterface ? $token->getGatewayToken() : null;
    }
}
