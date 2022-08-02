<?php

declare(strict_types=1);

namespace Worldline\Payment\Logger\ResourceModel\RequestLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Worldline\Payment\Logger\ResourceModel\RequestLog as RequestLogResource;
use Worldline\Payment\Logger\RequestLog as RequestLogModel;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(RequestLogModel::class, RequestLogResource::class);
    }

    public function getIdFieldName(): string
    {
        return 'id';
    }
}
