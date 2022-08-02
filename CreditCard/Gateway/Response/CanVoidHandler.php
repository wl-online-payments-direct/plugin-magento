<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Gateway\Response;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Worldline\Payment\Gateway\SubjectReader;

class CanVoidHandler implements ValueHandlerInterface
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
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $subject, $storeId = null): bool
    {
        $paymentDO = $this->subjectReader->readPayment($subject);
        $payment = $paymentDO->getPayment();

        return ($payment instanceof Payment) && !$payment->getAmountPaid();
    }
}
