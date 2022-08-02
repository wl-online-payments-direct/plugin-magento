<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Service\Creator;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\CreateHostedCheckoutRequest;
use OnlinePayments\Sdk\Domain\CreateHostedCheckoutRequestFactory;
use Worldline\Payment\HostedCheckout\Service\Creator\Request\OrderDataBuilder;
use Worldline\Payment\HostedCheckout\Service\Creator\Request\SpecificInputDataBuilder;
use Worldline\Payment\HostedCheckout\Service\Creator\Request\RedirectPaymentMethodSpecificInputDataBuilder;
use Worldline\Payment\HostedCheckout\Service\Creator\Request\CardPaymentMethodSpecificInputDataBuilder;

class RequestBuilder
{
    /**
     * @var CreateHostedCheckoutRequestFactory
     */
    private $createHostedCheckoutRequestFactory;

    /**
     * @var OrderDataBuilder
     */
    private $orderDataBuilder;

    /**
     * @var SpecificInputDataBuilder
     */
    private $specificInputDataBuilder;

    /**
     * @var RedirectPaymentMethodSpecificInputDataBuilder
     */
    private $redirectPaymentMethodSpecificInputDataBuilder;

    /**
     * @var CardPaymentMethodSpecificInputDataBuilder
     */
    private $cardPaymentMethodSpecificInputDataBuilder;

    public function __construct(
        CreateHostedCheckoutRequestFactory $createHostedCheckoutRequestFactory,
        OrderDataBuilder $orderDataBuilder,
        SpecificInputDataBuilder $specificInputDataBuilder,
        RedirectPaymentMethodSpecificInputDataBuilder $redirectPaymentMethodSpecificInputDataBuilder,
        CardPaymentMethodSpecificInputDataBuilder $cardPaymentMethodSpecificInputDataBuilder
    ) {
        $this->createHostedCheckoutRequestFactory = $createHostedCheckoutRequestFactory;
        $this->orderDataBuilder = $orderDataBuilder;
        $this->specificInputDataBuilder = $specificInputDataBuilder;
        $this->redirectPaymentMethodSpecificInputDataBuilder = $redirectPaymentMethodSpecificInputDataBuilder;
        $this->cardPaymentMethodSpecificInputDataBuilder = $cardPaymentMethodSpecificInputDataBuilder;
    }

    /**
     * @param CartInterface $quote
     * @return CreateHostedCheckoutRequest
     */
    public function build(CartInterface $quote): CreateHostedCheckoutRequest
    {
        $createHostedCheckoutRequest = $this->createHostedCheckoutRequestFactory->create();
        $createHostedCheckoutRequest->setOrder($this->orderDataBuilder->build($quote));
        $createHostedCheckoutRequest->setHostedCheckoutSpecificInput($this->specificInputDataBuilder->build());
        $createHostedCheckoutRequest->setRedirectPaymentMethodSpecificInput(
            $this->redirectPaymentMethodSpecificInputDataBuilder->build()
        );
        $createHostedCheckoutRequest->setCardPaymentMethodSpecificInput(
            $this->cardPaymentMethodSpecificInputDataBuilder->build($quote)
        );

        return $createHostedCheckoutRequest;
    }
}
