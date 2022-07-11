<?php

declare(strict_types=1);

namespace Worldline\Payment\Webhook;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Worldline\Payment\Api\Data\PaymentStatusInterface;
use Worldline\Payment\Api\Data\PaymentStatusInterfaceFactory;
use Worldline\Payment\Model\Config\OrderStatusUpdater as WebhookConfig;
use Worldline\Payment\Model\Order\StatusUpdateProcessor;

class WebhookProcessor
{
    /**
     * @var OrderInterfaceFactory
     */
    private $orderFactory;

    /**
     * @var StatusUpdateProcessor
     */
    private $statusUpdater;

    /**
     * @var WebhookConfig
     */
    private $webhookConfig;

    /**
     * @var PaymentStatusInterfaceFactory
     */
    private $paymentStatusFactory;

    /**
     * @param OrderInterfaceFactory $orderFactory
     * @param StatusUpdateProcessor $statusUpdater
     * @param WebhookConfig $webhookConfig
     * @param PaymentStatusInterfaceFactory $paymentStatusFactory
     */
    public function __construct(
        OrderInterfaceFactory $orderFactory,
        StatusUpdateProcessor $statusUpdater,
        WebhookConfig $webhookConfig,
        PaymentStatusInterfaceFactory $paymentStatusFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->statusUpdater = $statusUpdater;
        $this->webhookConfig = $webhookConfig;
        $this->paymentStatusFactory = $paymentStatusFactory;
    }

    /**
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function process(array $data)
    {
        if (!$this->webhookConfig->isReceivingWebhooksAllowed()) {
            return;
        }

        $orderIncrementId = $data['payment']['paymentOutput']['references']['merchantReference'] ?? false;
        $paymentStatus = $this->getPaymentStatus($data);

        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
        if (!$order->getPayment()) {
            throw new LocalizedException(__('No payment method'));
        }

        $this->statusUpdater->updateOrderStatus($order, $paymentStatus);
    }

    /**
     * @param array $data
     * @return PaymentStatusInterface
     */
    private function getPaymentStatus(array $data): PaymentStatusInterface
    {
        $eciCode =
            $data['payment']['paymentOutput']['cardPaymentMethodSpecificOutput']['threeDSecureResults']['eci'] ?? null;

        return $this->paymentStatusFactory->create([
            'status' => (string) ($data['payment']['status'] ?? ''),
            'statusCode' => (int) ($data['payment']['statusOutput']['statusCode'] ?? 0),
            'eciCode' => $eciCode,
        ]);
    }
}
