<?php
declare(strict_types=1);

namespace Worldline\Payment\Cron;

use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Model\Log\ResourceModel\Log;
use Worldline\Payment\Model\WorldlineConfig;

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
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @param Log $logResource
     * @param DateTime $dateTime
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     * @param WorldlineConfig $worldlineConfig
     */
    public function __construct(
        Log $logResource,
        DateTime $dateTime,
        TimezoneInterface $timezone,
        LoggerInterface $logger,
        WorldlineConfig $worldlineConfig
    ) {
        $this->logResource = $logResource;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->worldlineConfig = $worldlineConfig;
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
        $date = $this->dateTime->formatDate($this->timezone->scopeTimeStamp() + $offset);

        try {
            $this->logResource->clearRecordsByDate($date);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
