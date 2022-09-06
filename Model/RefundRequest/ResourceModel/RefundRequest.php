<?php
declare(strict_types=1);

namespace Worldline\Payment\Model\RefundRequest\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Worldline\Payment\Api\Data\RefundRequestInterface;

/**
 * @core
 */
class RefundRequest extends AbstractDb
{
    public const TABLE_NAME = 'worldline_payment_refund_request';

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, RefundRequestInterface::ENTITY_ID);
    }
}
