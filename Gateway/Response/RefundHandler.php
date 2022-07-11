<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Response;

use Magento\Sales\Model\Order\Payment;

class RefundHandler extends VoidHandler
{
    /**
     * Whether parent transaction should be closed
     *
     * @param Payment $orderPayment
     * @return bool
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment): bool
    {
        return !$orderPayment->getCreditmemo()->getInvoice()->canRefund();
    }
}
