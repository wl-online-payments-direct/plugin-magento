<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;
use OnlinePayments\Sdk\Domain\AmountOfMoney;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Gateway\SubjectReader;

class RefundDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(SubjectReader $subjectReader, LoggerInterface $logger)
    {
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        $amount = null;
        try {
            $amount = (int) ($this->subjectReader->readAmount($buildSubject) * 100);
            $currencyCode = $payment->getOrder()->getOrderCurrencyCode();
        } catch (\InvalidArgumentException $e) {
            $this->logger->debug($e->getMessage(), $e->getTrace());
        }

        $amountOfMoney = new AmountOfMoney();
        $amountOfMoney->setAmount($amount);
        $amountOfMoney->setCurrencyCode($currencyCode);

        /**
         * We should remember that Payment sets Capture txn id of current Invoice into ParentTransactionId Field
         */
        $txnId = str_replace(
            '-' . TransactionInterface::TYPE_CAPTURE,
            '',
            $payment->getParentTransactionId() ?: $payment->getLastTransId()
        );

        return [
            'transaction_id' => $txnId,
            PaymentDataBuilder::AMOUNT => $amountOfMoney
        ];
    }
}
