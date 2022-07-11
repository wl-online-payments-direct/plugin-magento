<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\HostedCheckout;

use Magento\Framework\Exception\LocalizedException;
use Worldline\Payment\Model\Order\StatusManager;
use Worldline\Payment\Model\PaymentProvider;

class OrderCanceler
{
    /**
     * @var PaymentProvider
     */
    private $paymentProvider;

    /**
     * @var StatusManager
     */
    private $statusManager;

    /**
     * @param PaymentProvider $paymentProvider
     * @param StatusManager $statusManager
     */
    public function __construct(PaymentProvider $paymentProvider, StatusManager $statusManager)
    {
        $this->paymentProvider = $paymentProvider;
        $this->statusManager = $statusManager;
    }

    /**
     * @param string $paymentId
     * @return void
     */
    public function cancelByWorldlinePaymentId(string $paymentId)
    {
        $payment = $this->paymentProvider->getPayment($paymentId);
        if (!$payment) {
            return;
        }

        try {
            $this->statusManager->cancelOrder($payment->getOrder());
        } catch (LocalizedException $e) {
            return;
        }
    }
}
