<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Service\Creator\Request;

use OnlinePayments\Sdk\Domain\RedirectPaymentMethodSpecificInputFactory;
use Worldline\Payment\HostedCheckout\Gateway\Config\Config;

class RedirectPaymentMethodSpecificInputDataBuilder
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var RedirectPaymentMethodSpecificInputFactory
     */
    private $redirectPaymentMethodSpecificInputFactory;

    public function __construct(
        Config $config,
        RedirectPaymentMethodSpecificInputFactory $redirectPaymentMethodSpecificInputFactory
    ) {
        $this->config = $config;
        $this->redirectPaymentMethodSpecificInputFactory = $redirectPaymentMethodSpecificInputFactory;
    }

    public function build()
    {
        $redirectPaymentMethodSpecificInput = $this->redirectPaymentMethodSpecificInputFactory->create();
        $authMode = $this->config->getAuthorizationMode();
        $redirectPaymentMethodSpecificInput->setRequiresApproval($authMode !== Config::AUTHORIZATION_MODE_SALE);

        return $redirectPaymentMethodSpecificInput;
    }
}
