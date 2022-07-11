<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Response;

use Magento\Sales\Model\Order\Payment;
use OnlinePayments\Sdk\DataObject;

class VoidHandler extends TransactionIdHandler
{
    /**
     * @param Payment $orderPayment
     * @param DataObject $transaction
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
     */
    protected function setTransactionId(Payment $orderPayment, DataObject $transaction)
    {
    }

    /**
     * Whether transaction should be closed
     *
     * @return bool
     */
    protected function shouldCloseTransaction(): bool
    {
        return true;
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
        return true;
    }
}
