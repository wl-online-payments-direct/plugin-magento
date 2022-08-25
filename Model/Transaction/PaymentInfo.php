<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Transaction;

use Worldline\Payment\Api\Data\PaymentInfoInterface;

class PaymentInfo implements PaymentInfoInterface
{
    /**
     * @var string
     */
    private $status = '';

    /**
     * @var int|null
     */
    private $statusCode;

    /**
     * @var string
     */
    private $transactionId = '';

    /**
     * @var float
     */
    private $authorizedAmount = 0.0;

    /**
     * @var float|null
     */
    private $amountAvailableForCapture;

    /**
     * @var float|null
     */
    private $refundedAmount;

    /**
     * @var float|null
     */
    private $amountAvailableForRefund;

    /**
     * @var string
     */
    private $paymentMethod = '';

    /**
     * @var string
     */
    private $fraudResult = '';

    /**
     * @var string
     */
    private $cardLastNumbers = '';

    /**
     * @var int
     */
    private $paymentProductId;

    /**
     * @var string
     */
    private $currency = '';

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): PaymentInfoInterface
    {
        $this->status = $status;
        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): PaymentInfoInterface
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getLastTransactionNumber(): string
    {
        return $this->transactionId;
    }

    public function setLastTransactionNumber(string $transactionId): PaymentInfoInterface
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    public function getAuthorizedAmount(): float
    {
        return $this->authorizedAmount;
    }

    public function setAuthorizedAmount(float $amount): PaymentInfoInterface
    {
        $this->authorizedAmount = $amount;
        return $this;
    }

    public function getAmountAvailableForCapture(): ?float
    {
        return $this->amountAvailableForCapture;
    }

    public function setAmountAvailableForCapture(?float $amount): PaymentInfoInterface
    {
        $this->amountAvailableForCapture = $amount;
        return $this;
    }

    public function getRefundedAmount(): ?float
    {
        return $this->refundedAmount;
    }

    public function setRefundedAmount(?float $amount): PaymentInfoInterface
    {
        $this->refundedAmount = $amount;
        return $this;
    }

    public function getAmountAvailableForRefund(): ?float
    {
        return $this->amountAvailableForRefund;
    }

    public function setAmountAvailableForRefund(?float $amount): PaymentInfoInterface
    {
        $this->amountAvailableForRefund = $amount;
        return $this;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): PaymentInfoInterface
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getFraudResult(): string
    {
        return $this->fraudResult;
    }

    public function setFraudResult(string $fraudResult): PaymentInfoInterface
    {
        $this->fraudResult = $fraudResult;
        return $this;
    }

    public function getCardLastNumbers(): string
    {
        return $this->cardLastNumbers;
    }

    public function setCardLastNumbers(string $cardNumbers): PaymentInfoInterface
    {
        $this->cardLastNumbers = $cardNumbers;
        return $this;
    }

    public function getPaymentProductId(): ?int
    {
        return $this->paymentProductId;
    }

    public function setPaymentProductId(int $paymentProductId): PaymentInfoInterface
    {
        $this->paymentProductId = $paymentProductId;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): PaymentInfoInterface
    {
        $this->currency = $currency;
        return $this;
    }
}
