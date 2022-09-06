<?php

declare(strict_types=1);

namespace Worldline\Payment\Api;

use Worldline\Payment\Api\Data\TransactionInterface;

interface TransactionRepositoryInterface
{
    public function save(TransactionInterface $refundRequest): TransactionInterface;
    public function getLastTransaction(string $incrementId): ?TransactionInterface;
    public function getAuthorizeTransaction(string $incrementId): ?TransactionInterface;
    public function getCaptureTransaction(string $incrementId): ?TransactionInterface;
    public function getRefundedTransactions(string $incrementId): array;
    public function getPendingRefundTransactions(string $incrementId): array;
}
