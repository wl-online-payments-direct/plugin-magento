<?php
declare(strict_types=1);

namespace Worldline\Payment\Model\RefundRequest;

use Magento\Framework\Model\AbstractModel;
use Worldline\Payment\Api\Data\RefundRequestInterface;
use Worldline\Payment\Model\RefundRequest\ResourceModel\RefundRequest as RefundRequestResource;

/**
 * @core
 */
class RefundRequest extends AbstractModel implements RefundRequestInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = RefundRequestResource::TABLE_NAME;

    protected function _construct(): void
    {
        $this->_init(RefundRequestResource::class);
    }

    /**
     * @return int|string|null
     */
    public function getId()
    {
        return $this->_getData(self::ENTITY_ID);
    }

    public function getInvoiceId(): int
    {
        return (int)$this->_getData(self::INVOICE_ID);
    }

    public function setInvoiceId(int $invoiceId): RefundRequestInterface
    {
        $this->setData(self::INVOICE_ID, $invoiceId);
        return $this;
    }

    public function getIncrementId(): string
    {
        return $this->_getData(self::INCREMENT_ID);
    }

    public function setIncrementId(string $incrementId): RefundRequestInterface
    {
        $this->setData(self::INCREMENT_ID, $incrementId);
        return $this;
    }

    public function getCreditMemoId(): int
    {
        return (int)$this->_getData(self::CREDITMEMO_ID);
    }

    public function setCreditMemoId(int $creditMemoId): RefundRequestInterface
    {
        $this->setData(self::CREDITMEMO_ID, $creditMemoId);
        return $this;
    }

    public function getAmount(): int
    {
        return (int)$this->_getData(self::AMOUNT);
    }

    public function setAmount(int $amount): RefundRequestInterface
    {
        $this->setData(self::AMOUNT, $amount);
        return $this;
    }

    public function isRefunded(): bool
    {
        return (bool)$this->_getData(self::REFUNDED);
    }

    public function setRefunded(bool $refunded): RefundRequestInterface
    {
        $this->setData(self::REFUNDED, $refunded);
        return $this;
    }
}
