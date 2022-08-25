<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Service\Creator\Request;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\CreateMandateRequestFactory;
use OnlinePayments\Sdk\Domain\SepaDirectDebitPaymentMethodSpecificInputBase;
use OnlinePayments\Sdk\Domain\SepaDirectDebitPaymentMethodSpecificInputBaseFactory;
use OnlinePayments\Sdk\Domain\SepaDirectDebitPaymentProduct771SpecificInputBaseFactory;
use Worldline\Payment\HostedCheckout\Gateway\Config\Config;

class SepaDirectDebitPaymentMethodSpecificInputBuilder
{
    /**
     * @var SepaDirectDebitPaymentMethodSpecificInputBaseFactory
     */
    private $debitPaymentMethodSpecificInputBaseFactory;

    /**
     * @var SepaDirectDebitPaymentProduct771SpecificInputBaseFactory
     */
    private $debitPaymentProduct771SpecificInputBaseFactory;

    /**
     * @var CreateMandateRequestFactory
     */
    private $createMandateRequestFactory;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        SepaDirectDebitPaymentMethodSpecificInputBaseFactory $debitPaymentMethodSpecificInputBaseFactory,
        SepaDirectDebitPaymentProduct771SpecificInputBaseFactory $debitPaymentProduct771SpecificInputBaseFactory,
        CreateMandateRequestFactory $createMandateRequestFactory,
        Config $config
    ) {
        $this->debitPaymentMethodSpecificInputBaseFactory = $debitPaymentMethodSpecificInputBaseFactory;
        $this->debitPaymentProduct771SpecificInputBaseFactory = $debitPaymentProduct771SpecificInputBaseFactory;
        $this->createMandateRequestFactory = $createMandateRequestFactory;
        $this->config = $config;
    }

    public function build(CartInterface $quote): SepaDirectDebitPaymentMethodSpecificInputBase
    {
        /** @var SepaDirectDebitPaymentMethodSpecificInputBase $debitPaymentMethodSpecificInput */
        $debitPaymentMethodSpecificInput = $this->debitPaymentMethodSpecificInputBaseFactory->create();

        $paymentProduct = $this->debitPaymentProduct771SpecificInputBaseFactory->create();
        $mandate = $this->createMandateRequestFactory->create();

        $mandate->setCustomerReference($quote->getReservedOrderId());
        $mandate->setRecurrenceType($this->config->getDirectDebitRecurrenceType());
        $mandate->setSignatureType($this->config->getDirectDebitSignatureType());

        $paymentProduct->setMandate($mandate);

        $debitPaymentMethodSpecificInput->setPaymentProduct771SpecificInput($paymentProduct);

        return $debitPaymentMethodSpecificInput;
    }
}
