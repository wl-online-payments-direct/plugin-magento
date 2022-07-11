<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Log\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Worldline\Payment\Api\Data\LogInterface;

class Log extends AbstractDb
{
    public const TABLE_NAME = 'worldline_payment_log';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, LogInterface::LOG_ID);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function clearTable(): void
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }

    /**
     * @param string $date
     * @return void
     * @throws LocalizedException
     */
    public function clearRecordsByDate(string $date): void
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            [LogInterface::CREATED_AT . ' <= ?' => $date]
        );
    }
}
