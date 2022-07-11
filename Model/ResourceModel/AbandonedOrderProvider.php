<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\ResourceModel;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Worldline\Payment\Model\Config\OrderStatusUpdater as OrderStatusConfig;

class AbandonedOrderProvider
{
    /**
     * @var string[]
     */
    private $allowedPaymentMethods = [
        'worldline_cc',
        'worldline_cc_vault',
        'worldline_hosted_checkout',
        'worldline_hosted_checkout_vault'
    ];

    /**
     * @var string[]
     */
    private $allowedOrderStates = [
        Order::STATE_PAYMENT_REVIEW,
        Order::STATE_NEW,
    ];

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var OrderStatusConfig
     */
    private $orderStatusConfig;

    /**
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param DateTime $dateTime
     * @param OrderStatusConfig $orderStatusConfig
     */
    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        DateTime $dateTime,
        OrderStatusConfig $orderStatusConfig
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->dateTime = $dateTime;
        $this->orderStatusConfig = $orderStatusConfig;
    }

    /**
     * @return OrderInterface[]
     */
    public function getOrders(): array
    {
        if (!$this->orderStatusConfig->getWaitingTime()) {
            return [];
        }

        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('state', ['in' => $this->allowedOrderStates]);
        $this->addTimeToFilter($collection);
        $this->addAllowedPaymentMethods($collection);

        return $collection->getItems();
    }

    /**
     * @param OrderCollection $collection
     * @return AbandonedOrderProvider
     */
    private function addTimeToFilter(OrderCollection $collection): AbandonedOrderProvider
    {
        $timestampNow = $this->dateTime->gmtTimestamp();
        $dateTo = date(
            'Y-m-d H:i:s',
            $timestampNow - $this->orderStatusConfig->getWaitingTime() * 60
        );
        $collection->addFieldToFilter('created_at', ['lteq' => $dateTo]);

        return $this;
    }

    /**
     * @param OrderCollection $collection
     * @return AbandonedOrderProvider
     */
    private function addAllowedPaymentMethods(OrderCollection $collection): AbandonedOrderProvider
    {
        $collection->getSelect()
            ->join(
                ["sop" => $collection->getTable("sales_order_payment")],
                'main_table.entity_id = sop.parent_id',
                ['method']
            )
            ->where('sop.method IN (?)', $this->allowedPaymentMethods);

        return $this;
    }
}
