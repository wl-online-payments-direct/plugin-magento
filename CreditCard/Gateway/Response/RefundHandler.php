<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use OnlinePayments\Sdk\Domain\RefundResponse as WorldlineResponse;
use Worldline\Payment\Gateway\SubjectReader;
use Worldline\Payment\Model\AdditionalInfoInterface;

class RefundHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        if ($paymentDO->getPayment() instanceof Payment) {
            /** @var Payment $orderPayment */
            $orderPayment = $paymentDO->getPayment();
            $this->fillAdditionalInfo($orderPayment, $response['object']);
            $orderPayment->setIsTransactionClosed($this->shouldCloseTransaction());
            $closed = $this->shouldCloseParentTransaction($orderPayment);
            $orderPayment->setShouldCloseParentTransaction($closed);
        }
    }

    protected function shouldCloseTransaction(): bool
    {
        return true;
    }

    protected function shouldCloseParentTransaction(Payment $orderPayment): bool
    {
        return !$orderPayment->getCreditmemo()->getInvoice()->canRefund();
    }

    //TODO: Need to work around with cases of multiple partial refunds (to not override stored data)
    private function fillAdditionalInfo(Payment $orderPayment, WorldlineResponse $response): void
    {
        $amountModel = $response->getRefundOutput()->getAmountOfMoney();

        $orderPayment->setAdditionalInformation(
            AdditionalInfoInterface::KEY_REFUND_TRANSACTION_ID,
            $response->getId()
        )->setAdditionalInformation(
            AdditionalInfoInterface::KEY_STATUS,
            $response->getStatus()
        )->setAdditionalInformation(
            AdditionalInfoInterface::KEY_STATUS_CODE,
            $response->getStatusOutput()->getStatusCode()
        )->setAdditionalInformation(
            AdditionalInfoInterface::KEY_REFUND_AMOUNT,
            ($amountModel->getAmount() / 100) . ' ' . $amountModel->getCurrencyCode()
        );
    }
}
