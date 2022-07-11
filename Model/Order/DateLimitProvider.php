<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Order;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Worldline\Payment\Model\Config\OrderStatusUpdater as OrderStatusConfig;

class DateLimitProvider
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var OrderStatusConfig
     */
    private $orderStatusConfig;

    /**
     * @param DateTime $dateTime
     * @param OrderStatusConfig $orderStatusConfig
     */
    public function __construct(
        DateTime $dateTime,
        OrderStatusConfig $orderStatusConfig
    ) {
        $this->dateTime = $dateTime;
        $this->orderStatusConfig = $orderStatusConfig;
    }

    /**
     * @return ?string
     */
    public function getDateTo(): ?string
    {
        if (!$this->orderStatusConfig->getFallbackTimeout()) {
            return null;
        }

        $timestampNow = $this->dateTime->gmtTimestamp();
        return date(
            'Y-m-d H:i:s',
            $timestampNow - $this->orderStatusConfig->getFallbackTimeout() * 60
        );
    }

    /**
     * @return ?string
     */
    public function getDateFrom(): ?string
    {
        if (!$this->orderStatusConfig->getFallbackTimeoutLimit()) {
            return null;
        }

        $timestampNow = $this->dateTime->gmtTimestamp();
        return date(
            'Y-m-d H:i:s',
            $timestampNow - $this->orderStatusConfig->getFallbackTimeoutLimit() * 60 * 60
        );
    }
}
