<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Order;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Worldline\Payment\Api\Data\PaymentStatusInterface;
use Worldline\Payment\Model\HostedCheckout\OrderInitiation;
use Worldline\Payment\Model\Order\StatusManager as OrderStatusManager;
use Worldline\Payment\Model\Ui\HostedCheckout\ConfigProvider as HostedCheckoutConfigProvider;

class StatusUpdateProcessor
{
    public const AUTHORIZE_CODE = 5;
    public const CAPTURE_CODE = 9;
    public const REFUNDED_STATUS_CODES = [7, 8, 85];
    public const CANCELLED_BY_CONSUMER = 'CANCELLED_BY_CONSUMER';

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $orderStatusHistoryRepository;

    /**
     * @var StatusManager
     */
    private $orderStatusManager;

    /**
     * @var OrderInitiation
     */
    private $orderInitiation;

    /**
     * @param OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
     * @param StatusManager $orderStatusManager
     * @param OrderInitiation $orderInitiation
     */
    public function __construct(
        OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository,
        OrderStatusManager $orderStatusManager,
        OrderInitiation $orderInitiation
    ) {
        $this->orderStatusManager = $orderStatusManager;
        $this->orderInitiation = $orderInitiation;
        $this->orderStatusHistoryRepository = $orderStatusHistoryRepository;
    }

    /**
     * @param OrderInterface $order
     * @param PaymentStatusInterface $paymentStatus
     * @return void
     * @throws LocalizedException
     */
    public function updateOrderStatus(OrderInterface $order, PaymentStatusInterface $paymentStatus)
    {
        if ($paymentStatus->getStatus() === self::CANCELLED_BY_CONSUMER) {
            $this->orderStatusManager->cancelOrder($order);
        }

        if ($this->isHostedCheckout($order) &&
            in_array($paymentStatus->getStatusCode(), [self::AUTHORIZE_CODE, self::CAPTURE_CODE])) {
            $order = $this->orderInitiation->init($order);
        }

        if ($this->isHostedCheckout($order) &&
            in_array($paymentStatus->getStatusCode(), self::REFUNDED_STATUS_CODES)) {
            $this->orderStatusManager->closeOrder($order);
        }

        $this->addStatusHistory($order, $paymentStatus);
    }

    /**
     * @param OrderInterface $order
     * @param PaymentStatusInterface $paymentStatus
     * @return void
     * @throws CouldNotSaveException
     */
    private function addStatusHistory(OrderInterface $order, PaymentStatusInterface $paymentStatus)
    {
        $message = __('Order status has been changed successfully. WorldLine status: %1.', $paymentStatus->getStatus());
        $message .= ' ' . __('Status Code: %1.', $paymentStatus->getStatusCode());
        if ($paymentStatus->getEciCode()) {
            $message .= ' ' . __('Code eci: %1.', $paymentStatus->getEciCode());
        }

        $order->addStatusToHistory($order->getStatus(), $message);
        $comment = $order->addCommentToStatusHistory($message);
        $this->orderStatusHistoryRepository->save($comment);
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    private function isHostedCheckout(OrderInterface $order): bool
    {
        return in_array($order->getPayment()->getMethod(), [
            HostedCheckoutConfigProvider::HC_CODE,
            HostedCheckoutConfigProvider::HC_VAULT_CODE
        ]);
    }
}
