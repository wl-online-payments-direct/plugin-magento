<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;
use OnlinePayments\Sdk\Domain\AmountOfMoneyFactory;
use OnlinePayments\Sdk\Domain\RefundRequest;
use OnlinePayments\Sdk\Domain\RefundRequestFactory;
use Worldline\Payment\Gateway\SubjectReader;

class RefundDataBuilder implements BuilderInterface
{
    public const TRANSACTION_ID = 'transaction_id';
    public const REFUND_REQUEST = 'refund_request';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var RefundRequestFactory
     */
    private $refundRequestFactory;

    /**
     * @var AmountOfMoneyFactory
     */
    private $amountOfMoneyFactory;

    public function __construct(
        SubjectReader $subjectReader,
        RefundRequestFactory $refundRequestFactory,
        AmountOfMoneyFactory $amountOfMoneyFactory
    ) {
        $this->subjectReader = $subjectReader;
        $this->refundRequestFactory = $refundRequestFactory;
        $this->amountOfMoneyFactory = $amountOfMoneyFactory;
    }

    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        // Payment sets Capture txn id of current Invoice into ParentTransactionId Field
        $txnId = str_replace(
            '-' . TransactionInterface::TYPE_CAPTURE,
            '',
            $payment->getParentTransactionId() ?: $payment->getLastTransId()
        );

        return [
            self::TRANSACTION_ID => $txnId,
            self::REFUND_REQUEST => $this->getRefundRequest($buildSubject, $payment),
        ];
    }

    private function getRefundRequest(array $buildSubject, Payment $payment): RefundRequest
    {
        $amount = (int) ($this->subjectReader->readAmount($buildSubject) * 100);
        $currencyCode = $payment->getOrder()->getOrderCurrencyCode();

        $amountOfMoney = $this->amountOfMoneyFactory->create();
        $amountOfMoney->setAmount($amount);
        $amountOfMoney->setCurrencyCode($currencyCode);

        $refundRequest = $this->refundRequestFactory->create();
        $refundRequest->setAmountOfMoney($amountOfMoney);
        return $refundRequest;
    }
}
