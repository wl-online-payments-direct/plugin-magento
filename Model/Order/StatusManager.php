<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;

class StatusManager
{
    /**
     * @var CreditmemoFactory
     */
    private $creditMemoFactory;

    /**
     * @var CreditmemoManagementInterface
     */
    private $creditMemoManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    /**
     * @param CreditmemoFactory $creditMemoFactory
     * @param CreditmemoManagementInterface $creditMemoManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     */
    public function __construct(
        CreditmemoFactory $creditMemoFactory,
        CreditmemoManagementInterface $creditMemoManagement,
        OrderRepositoryInterface $orderRepository,
        OrderPaymentRepositoryInterface $orderPaymentRepository
    ) {
        $this->creditMemoFactory = $creditMemoFactory;
        $this->creditMemoManagement = $creditMemoManagement;
        $this->orderRepository = $orderRepository;
        $this->orderPaymentRepository = $orderPaymentRepository;
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    public function closeOrder(OrderInterface $order)
    {
        foreach ($order->getInvoiceCollection()->getItems() as $invoice) {
            $creditMemo = $this->creditMemoFactory->createByOrder($order);
            $creditMemo->setInvoice($invoice);
            $this->creditMemoManagement->refund($creditMemo, true);
        }
        $this->orderRepository->save($order);
    }

    /**
     * @param OrderInterface $order
     * @return void
     * @throws LocalizedException
     */
    public function cancelOrder(OrderInterface $order)
    {
        $payment = $order->getPayment();
        $payment->setIsTransactionDenied(true);
        $payment->update();
        $this->orderPaymentRepository->save($payment);
        $this->orderRepository->save($order);
    }
}
