<?php

declare(strict_types=1);

namespace Worldline\Payment\Cron;

use Psr\Log\LoggerInterface;
use Worldline\Payment\Model\Order\StatusManager as OrderStatusManager;
use Worldline\Payment\Model\ResourceModel\AbandonedOrderProvider;

class AbandonedOrderCanceler
{
    /**
     * @var AbandonedOrderProvider
     */
    private $abandonedOrderProvider;

    /**
     * @var OrderStatusManager
     */
    private $orderStatusManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AbandonedOrderProvider $abandonedOrderProvider
     * @param OrderStatusManager $orderStatusManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        AbandonedOrderProvider $abandonedOrderProvider,
        OrderStatusManager $orderStatusManager,
        LoggerInterface $logger
    ) {
        $this->abandonedOrderProvider = $abandonedOrderProvider;
        $this->logger = $logger;
        $this->orderStatusManager = $orderStatusManager;
    }

    /**
     * @return void
     */
    public function execute()
    {
        foreach ($this->abandonedOrderProvider->getOrders() as $order) {
            try {
                $this->orderStatusManager->cancelOrder($order);
            } catch (\Exception $e) {
                $this->logger->warning(
                    $e->getMessage(),
                    ['Order cancellation error. Order_id' => $order->getId()]
                );
            }
        }
    }
}
