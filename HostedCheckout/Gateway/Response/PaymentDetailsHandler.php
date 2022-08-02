<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use OnlinePayments\Sdk\Domain\GetHostedCheckoutResponse;
use Worldline\Payment\Gateway\SubjectReader;

class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        /** @var GetHostedCheckoutResponse $response */
        $response = $response['object'] ?? false;
        if (!$response) {
            return;
        }

        $transaction = $response->getCreatedPaymentOutput()->getPayment();

        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();
        $payment->setCcTransId($transaction->getId());
        $payment->setLastTransId($transaction->getId());
        $payment->setCcStatusDescription($transaction->getStatus());
    }
}
