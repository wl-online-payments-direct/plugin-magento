<?php
declare(strict_types=1);

namespace Worldline\Payment\Model\Log;

use Magento\Framework\Model\AbstractModel;
use Worldline\Payment\Api\Data\LogInterface;
use Worldline\Payment\Model\Log\ResourceModel\Log as LogResource;

class Log extends AbstractModel implements LogInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = LogResource::TABLE_NAME;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(LogResource::class);
    }

    /**
     * @return int
     */
    public function getLogId(): int
    {
        return $this->_getData(self::LOG_ID);
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->_getData(self::CONTENT);
    }

    /**
     * @param string $content
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->setData(self::CONTENT, $content);
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * @param string $dateTime
     * @return void
     */
    public function setCreatedAt(string $dateTime): void
    {
        $this->setData(self::CREATED_AT, $dateTime);
    }
}
