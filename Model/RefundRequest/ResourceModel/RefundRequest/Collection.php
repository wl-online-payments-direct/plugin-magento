<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\RefundRequest\ResourceModel\RefundRequest;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Worldline\Payment\Model\RefundRequest\RefundRequest as RefundRequestModel;
use Worldline\Payment\Model\RefundRequest\ResourceModel\RefundRequest as RefundRequestResource;

/**
 * @core
 */
class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(RefundRequestModel::class, RefundRequestResource::class);
    }

    public function getIdFieldName(): string
    {
        return 'id';
    }
}
