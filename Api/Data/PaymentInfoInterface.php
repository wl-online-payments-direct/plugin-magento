<?php

declare(strict_types=1);

namespace Worldline\Payment\Api\Data;

interface PaymentInfoInterface
{
    public function getStatus(): string;
    public function setStatus(string $status): PaymentInfoInterface;

    public function getStatusCode(): ?int;
    public function setStatusCode(int $statusCode): PaymentInfoInterface;

    public function getLastTransactionNumber(): string;
    public function setLastTransactionNumber(string $transactionId): PaymentInfoInterface;

    public function getAuthorizedAmount(): float;
    public function setAuthorizedAmount(float $amount): PaymentInfoInterface;

    public function getAmountAvailableForCapture(): ?float;
    public function setAmountAvailableForCapture(?float $amount): PaymentInfoInterface;

    public function getRefundedAmount(): ?float;
    public function setRefundedAmount(?float $amount): PaymentInfoInterface;

    public function getAmountAvailableForRefund(): ?float;
    public function setAmountAvailableForRefund(?float $amount): PaymentInfoInterface;

    public function getPaymentMethod(): string;
    public function setPaymentMethod(string $paymentMethod): PaymentInfoInterface;

    public function getFraudResult(): string;
    public function setFraudResult(string $fraudResult): PaymentInfoInterface;

    public function getCardLastNumbers(): string;
    public function setCardLastNumbers(string $cardNumbers): PaymentInfoInterface;

    public function getPaymentProductId(): ?int;
    public function setPaymentProductId(int $paymentProductId): PaymentInfoInterface;

    public function getCurrency(): string;
    public function setCurrency(string $currency): PaymentInfoInterface;
}
