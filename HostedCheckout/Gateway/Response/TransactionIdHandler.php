<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use OnlinePayments\Sdk\DataObject;
use OnlinePayments\Sdk\Domain\CaptureResponse;
use OnlinePayments\Sdk\Domain\GetHostedCheckoutResponse;
use Worldline\Payment\Gateway\SubjectReader;

class TransactionIdHandler implements HandlerInterface
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
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        if ($paymentDO->getPayment() instanceof Payment) {
            /** @var DataObject $response */
            $response = $response['object'] ?? false;
            if (!$response) {
                return;
            }

            if ($response instanceof GetHostedCheckoutResponse) {
                $transaction = $response->getCreatedPaymentOutput()->getPayment();
            } elseif ($response instanceof CaptureResponse) {
                $transaction = $response;
            } else {
                throw new LocalizedException(__('Worldline Incorrect Response'));
            }

            $orderPayment = $paymentDO->getPayment();
            $this->setTransactionId($orderPayment, $transaction);

            $orderPayment->setIsTransactionClosed($this->shouldCloseTransaction());
            $closed = $this->shouldCloseParentTransaction($orderPayment);
            $orderPayment->setShouldCloseParentTransaction($closed);
        }
    }

    /**
     * @param Payment $orderPayment
     * @param DataObject $transaction
     * @return void
     */
    protected function setTransactionId(Payment $orderPayment, DataObject $transaction)
    {
        $orderPayment->setTransactionId($transaction->getId());
    }

    /**
     * Whether transaction should be closed
     *
     * @return bool
     */
    protected function shouldCloseTransaction(): bool
    {
        return false;
    }

    /**
     * Whether parent transaction should be closed
     *
     * @param Payment $orderPayment
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment): bool
    {
        return false;
    }
}
