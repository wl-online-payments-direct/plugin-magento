<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Transaction;

use Magento\Framework\Model\AbstractModel;
use Worldline\Payment\Api\Data\TransactionInterface;
use Worldline\Payment\Model\Transaction\ResourceModel\Transaction as TransactionResource;

class Transaction extends AbstractModel implements TransactionInterface
{
    protected function _construct(): void
    {
        $this->_init(TransactionResource::class);
    }

    public function getIncrementId(): ?string
    {
        return $this->getData(self::INCREMENT_ID);
    }

    public function setIncrementId(string $incrementId): TransactionInterface
    {
        $this->setData(self::INCREMENT_ID, $incrementId);
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus(string $status): TransactionInterface
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    public function getStatusCode(): ?int
    {
        return (int) $this->getData(self::STATUS_CODE);
    }

    public function setStatusCode(int $statusCode): TransactionInterface
    {
        $this->setData(self::STATUS_CODE, $statusCode);
        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    public function setTransactionId(string $transactionId): TransactionInterface
    {
        $this->setData(self::TRANSACTION_ID, $transactionId);
        return $this;
    }

    public function getAmount(): ?float
    {
        return (float) $this->getData(self::AMOUNT);
    }

    public function setAmount(float $amount): TransactionInterface
    {
        $this->setData(self::AMOUNT, $amount);
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->getData(self::CURRENCY);
    }

    public function setCurrency(string $currency): TransactionInterface
    {
        $this->setData(self::CURRENCY, $currency);
        return $this;
    }

    public function getAdditionalData(): ?array
    {
        return $this->getData(self::ADDITIONAL_DATA);
    }

    public function setAdditionalData(array $additionalData): TransactionInterface
    {
        $this->setData(self::ADDITIONAL_DATA, $additionalData);
        return $this;
    }
}
