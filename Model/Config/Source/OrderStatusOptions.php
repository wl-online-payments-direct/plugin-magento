<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Config\Source;

use Magento\Sales\Model\Config\Source\Order\Status;
use Magento\Sales\Model\Order;

class OrderStatusOptions extends Status
{
    /**
     * @var string[]
     */
    protected $_stateStatuses = [
        Order::STATE_PROCESSING
    ];
}
