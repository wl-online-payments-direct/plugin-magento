<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Order\Service;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Worldline\Payment\Model\Config\OrderStatusUpdater;
use Worldline\Payment\Model\HostedCheckout\OrderCanceler;
use Worldline\Payment\Model\HostedCheckout\OrderProcessor;
use Worldline\Payment\Model\PaymentProvider;

class HostedCheckoutProcessor
{
    /**
     * @var PaymentProvider
     */
    private $paymentProvider;

    /**
     * @var OrderProcessor
     */
    private $orderProcessor;

    /**
     * @var OrderStatusUpdater
     */
    private $orderStatusUpdater;

    /**
     * @var OrderCanceler
     */
    private $orderCanceler;

    /**
     * @param PaymentProvider $paymentProvider
     * @param OrderProcessor $orderProcessor
     * @param OrderStatusUpdater $orderStatusUpdater
     * @param OrderCanceler $orderCanceler
     */
    public function __construct(
        PaymentProvider $paymentProvider,
        OrderProcessor $orderProcessor,
        OrderStatusUpdater $orderStatusUpdater,
        OrderCanceler $orderCanceler
    ) {
        $this->paymentProvider = $paymentProvider;
        $this->orderProcessor = $orderProcessor;
        $this->orderStatusUpdater = $orderStatusUpdater;
        $this->orderCanceler = $orderCanceler;
    }

    /**
     * @param string $hostedCheckoutId
     * @param string $returnId
     * @return string|false
     * @throws Exception
     */
    public function process(string $hostedCheckoutId, string $returnId)
    {
        if (!$hostedCheckoutId || !$returnId) {
            return false;
        }

        try {
            if (!$this->orderStatusUpdater->isReceivingWebhooksAllowed()) {
                $this->orderProcessor->process($hostedCheckoutId, $returnId);
            } else {
                $this->orderProcessor->initHostedCheckoutResponse($hostedCheckoutId, $returnId);
            }

            $payment = $this->paymentProvider->getPayment($hostedCheckoutId);
            return $payment->getOrder()->getIncrementId();
        } catch (LocalizedException $exception) {
            $this->orderCanceler->cancelByWorldlinePaymentId($hostedCheckoutId);
            return false;
        }
    }
}
