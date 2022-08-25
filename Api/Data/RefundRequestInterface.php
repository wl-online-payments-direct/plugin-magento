<?php
declare(strict_types=1);

namespace Worldline\Payment\Api\Data;

interface RefundRequestInterface
{
    public const ENTITY_ID = 'id';
    public const INVOICE_ID = 'invoice_id';
    public const INCREMENT_ID = 'increment_id';
    public const CREDITMEMO_ID = 'creditmemo_id';
    public const AMOUNT = 'amount';
    public const REFUNDED = 'refunded';

    public function getId();

    public function getInvoiceId(): int;
    public function setInvoiceId(int $invoiceId): RefundRequestInterface;

    public function getIncrementId(): string;
    public function setIncrementId(string $incrementId): RefundRequestInterface;

    public function getCreditMemoId(): int;
    public function setCreditMemoId(int $creditMemoId): RefundRequestInterface;

    public function getAmount(): int;
    public function setAmount(int $amount): RefundRequestInterface;

    public function isRefunded(): bool;
    public function setRefunded(bool $refunded): RefundRequestInterface;
}
