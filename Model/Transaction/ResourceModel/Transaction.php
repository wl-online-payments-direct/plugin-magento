<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Transaction\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Transaction extends AbstractDb
{
    const TABLE_NAME = 'worldline_payment_transaction';

    /**
     * @var array
     */
    protected $_serializableFields = ['additional_data' => [null, []]];

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }
}
