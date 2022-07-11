<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\ResourceModel;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Worldline\Payment\Model\Order\DateLimitProvider;

class OrderProvider
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
        Order::STATE_NEW,
        Order::STATE_PENDING_PAYMENT,
        Order::STATE_PAYMENT_REVIEW,
    ];

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var DateLimitProvider
     */
    private $dateLimitProvider;

    /**
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param DateLimitProvider $dateLimitProvider
     */
    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        DateLimitProvider $dateLimitProvider
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->dateLimitProvider = $dateLimitProvider;
    }

    /**
     * @param string|null $incrementId
     * @return OrderInterface[]
     */
    public function getOrders(?string $incrementId = null): array
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('state', ['in' => $this->allowedOrderStates]);
        if ($incrementId) {
            $collection->addFieldToFilter('increment_id', ['=' => $incrementId]);
        }

        $this->addTimeToFilter($collection);
        $this->addAllowedPaymentMethods($collection);

        return $collection->getItems();
    }

    /**
     * @param OrderCollection $collection
     * @return void
     */
    private function addTimeToFilter(OrderCollection $collection)
    {
        if ($dateTo = $this->dateLimitProvider->getDateTo()) {
            $collection->addFieldToFilter('created_at', ['lteq' => $dateTo]);
        }

        if ($dateFrom = $this->dateLimitProvider->getDateFrom()) {
            $collection->addFieldToFilter('created_at', ['gteq' => $dateFrom]);
        }
    }

    /**
     * @param OrderCollection $collection
     * @return void
     */
    private function addAllowedPaymentMethods(OrderCollection $collection)
    {
        $collection->getSelect()
            ->join(
                ["sop" => $collection->getTable("sales_order_payment")],
                'main_table.entity_id = sop.parent_id',
                ['method']
            )
            ->where('sop.method IN (?)', $this->allowedPaymentMethods);
    }
}
