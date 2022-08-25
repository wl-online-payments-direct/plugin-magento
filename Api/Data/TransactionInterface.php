<?php

declare(strict_types=1);

namespace Worldline\Payment\Api\Data;

interface TransactionInterface
{
    public const INCREMENT_ID = 'increment_id';
    public const STATUS = 'status';
    public const STATUS_CODE = 'status_code';
    public const TRANSACTION_ID = 'transaction_id';
    public const AMOUNT = 'amount';
    public const CURRENCY = 'currency';
    public const CREATED_AT = 'created_at';
    public const ADDITIONAL_DATA = 'additional_data';

    public const FRAUD_RESULT = 'fraud_result';
    public const PAYMENT_METHOD = 'payment_method';
    public const CARD_LAST_4 = 'card_number';
    public const PAYMENT_PRODUCT_ID = 'payment_product_id';

    public function getIncrementId(): ?string;
    public function setIncrementId(string $incrementId): TransactionInterface;

    public function getStatus(): ?string;
    public function setStatus(string $status): TransactionInterface;

    public function getStatusCode(): ?int;
    public function setStatusCode(int $statusCode): TransactionInterface;

    public function getTransactionId(): ?string;
    public function setTransactionId(string $transactionId): TransactionInterface;

    public function getAmount(): ?float;
    public function setAmount(float $amount): TransactionInterface;

    public function getCurrency(): ?string;
    public function setCurrency(string $currency): TransactionInterface;

    public function getAdditionalData(): ?array;
    public function setAdditionalData(array $additionalData): TransactionInterface;
}
