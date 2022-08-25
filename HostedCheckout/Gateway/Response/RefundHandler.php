<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Worldline\Payment\Gateway\SubjectReader;

class RefundHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        if ($paymentDO->getPayment() instanceof Payment) {
            /** @var Payment $orderPayment */
            $orderPayment = $paymentDO->getPayment();
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
        if (!$orderPayment->getCreditmemo()) {
            return false;
        }

        return !$orderPayment->getCreditmemo()->getInvoice()->canRefund();
    }
}
