<?php
declare(strict_types=1);

namespace Worldline\Payment\Cron;

use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Logger\ResourceModel\RequestLog;
use Worldline\Payment\Model\Config\WorldlineConfig;
use Worldline\Payment\Model\Log\ResourceModel\Log;

class LoggingRecordsCleaner
{
    public const SEC_IN_DAY = 86400;

    /**
     * @var Log
     */
    private $logResource;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Worldline\Payment\Model\Config\WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var RequestLog
     */
    private $requestLog;

    /**
     * @param Log $logResource
     * @param DateTime $dateTime
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     * @param \Worldline\Payment\Model\Config\WorldlineConfig $worldlineConfig
     * @param RequestLog $requestLog
     */
    public function __construct(
        Log $logResource,
        DateTime $dateTime,
        TimezoneInterface $timezone,
        LoggerInterface $logger,
        WorldlineConfig $worldlineConfig,
        RequestLog $requestLog
    ) {
        $this->logResource = $logResource;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->worldlineConfig = $worldlineConfig;
        $this->requestLog = $requestLog;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $days = $this->worldlineConfig->getLoggingLifetime();
        if ($days === null) {
            return;
        }

        $offset = (int)$days * self::SEC_IN_DAY;
        $date = $this->dateTime->formatDate($this->timezone->scopeTimeStamp() - $offset);

        try {
            $this->logResource->clearRecordsByDate($date);
            $this->requestLog->clearRecordsByDate($date);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
