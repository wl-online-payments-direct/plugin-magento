<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Service\Creator;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\CreatePaymentRequest;
use OnlinePayments\Sdk\Domain\CreatePaymentRequestFactory;
use Worldline\Payment\CreditCard\Service\Creator\Request\CardPaymentMethodSpecificInputDataBuilder;
use Worldline\Payment\CreditCard\Service\Creator\Request\OrderDataBuilder;

class RequestBuilder
{
    /**
     * @var CreatePaymentRequestFactory
     */
    private $createPaymentRequestFactory;

    /**
     * @var OrderDataBuilder
     */
    private $orderDataBuilder;

    /**
     * @var CardPaymentMethodSpecificInputDataBuilder
     */
    private $cardPaymentMethodSpecificInputDataBuilder;

    public function __construct(
        CreatePaymentRequestFactory $createPaymentRequestFactory,
        OrderDataBuilder $orderDataBuilder,
        CardPaymentMethodSpecificInputDataBuilder $cardPaymentMethodSpecificInputDataBuilder
    ) {
        $this->createPaymentRequestFactory = $createPaymentRequestFactory;
        $this->orderDataBuilder = $orderDataBuilder;
        $this->cardPaymentMethodSpecificInputDataBuilder = $cardPaymentMethodSpecificInputDataBuilder;
    }

    public function build(CartInterface $quote): CreatePaymentRequest
    {
        $createPaymentRequest = $this->createPaymentRequestFactory->create();
        $createPaymentRequest->setOrder($this->orderDataBuilder->build($quote));
        $createPaymentRequest->setCardPaymentMethodSpecificInput(
            $this->cardPaymentMethodSpecificInputDataBuilder->build($quote)
        );

        return $createPaymentRequest;
    }
}
