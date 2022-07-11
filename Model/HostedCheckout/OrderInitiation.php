<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\HostedCheckout;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Worldline\Payment\Model\Ui\HostedCheckout\ConfigProvider;

class OrderInitiation
{
    /**
     * @var OrderProcessor
     */
    private $orderProcessor;

    /**
     * @param OrderProcessor $orderProcessor
     */
    public function __construct(OrderProcessor $orderProcessor)
    {
        $this->orderProcessor = $orderProcessor;
    }

    /**
     * @param Order $order
     * @return Order|null
     */
    public function init(Order $order): ?Order
    {
        $payment = $order->getPayment();
        $hostedCheckoutId = (string) $payment->getWorldlinePaymentId();
        $returnId = (string) $payment->getAdditionalInformation('RETURNMAC');

        if (!in_array($payment->getMethod(), [ConfigProvider::HC_CODE, ConfigProvider::HC_VAULT_CODE])
            || ($order->getState() !== Order::STATE_NEW)
            || !$hostedCheckoutId
            || !$returnId
        ) {
            return null;
        }

        try {
            return $this->orderProcessor->process($hostedCheckoutId, $returnId);
        } catch (LocalizedException $exception) {
            return null;
        }
    }
}
