<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Order\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Api\Data\PaymentStatusInterface;
use Worldline\Payment\Api\Data\PaymentStatusInterfaceFactory;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\Order\StatusUpdateProcessor;
use Worldline\Payment\Model\ResourceModel\OrderProvider;
use Worldline\Payment\Model\Ui\HostedCheckout\ConfigProvider;
use Worldline\Payment\Model\WorldlineConfig;

class WorldLineApiProcessor
{
    /**
     * @var ClientProvider
     */
    private $clientProvider;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var OrderProvider
     */
    private $orderProvider;

    /**
     * @var StatusUpdateProcessor
     */
    private $statusUpdater;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string[]
     */
    private $hostedCheckoutPaymentMethods = [
        ConfigProvider::HC_CODE,
        ConfigProvider::HC_VAULT_CODE
    ];

    /**
     * @var PaymentStatusInterfaceFactory
     */
    private $paymentStatusFactory;

    /**
     * @param ClientProvider $clientProvider
     * @param WorldlineConfig $worldlineConfig
     * @param OrderProvider $orderProvider
     * @param StatusUpdateProcessor $statusUpdater
     * @param LoggerInterface $logger
     * @param PaymentStatusInterfaceFactory $paymentStatusFactory
     */
    public function __construct(
        ClientProvider $clientProvider,
        WorldlineConfig $worldlineConfig,
        OrderProvider $orderProvider,
        StatusUpdateProcessor $statusUpdater,
        LoggerInterface $logger,
        PaymentStatusInterfaceFactory $paymentStatusFactory
    ) {
        $this->clientProvider = $clientProvider;
        $this->worldlineConfig = $worldlineConfig;
        $this->orderProvider = $orderProvider;
        $this->statusUpdater = $statusUpdater;
        $this->logger = $logger;
        $this->paymentStatusFactory = $paymentStatusFactory;
    }

    /**
     * @param string|null $incrementOrderId
     * @return void
     */
    public function process(?string $incrementOrderId = null)
    {
        foreach ($this->orderProvider->getOrders($incrementOrderId) as $order) {
            try {
                if (!$order->getPayment() instanceof OrderPaymentInterface) {
                    throw new LocalizedException(__('No payment method'));
                }

                $paymentStatus = $this->getStatus($order->getPayment(), (int) $order->getStoreId());
                $this->statusUpdater->updateOrderStatus($order, $paymentStatus);
            } catch (\Exception $exception) {
                $this->logger->warning($exception->getMessage(), ['order_id' => $order->getId()]);
            }
        }
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param int $storeId
     * @return PaymentStatusInterface
     * @throws \Exception
     */
    private function getStatus(OrderPaymentInterface $payment, int $storeId): PaymentStatusInterface
    {
        $merchantId = $this->worldlineConfig->getMerchantId($storeId);
        if (in_array($payment->getMethod(), $this->hostedCheckoutPaymentMethods)) {
            $paymentDetails = $this->clientProvider->getClient()
                ->merchant($merchantId)
                ->hostedCheckout()
                ->getHostedCheckout($payment->getWorldlinePaymentId());

            $payment = $paymentDetails->getCreatedPaymentOutput()->getPayment();
            return $this->paymentStatusFactory->create([
                'status' => $payment->getStatus(),
                'statusCode' => (int) $payment->getStatusOutput()->getStatusCode(),
                'eciCode' => $this->getEciCode($payment),
            ]);
        }

        $paymentDetails = $this->clientProvider->getClient()
            ->merchant($merchantId)
            ->payments()
            ->getPaymentDetails($payment->getLastTransId());

        return $this->paymentStatusFactory->create([
            'status' => $paymentDetails->getStatus(),
            'statusCode' => (int) $paymentDetails->getStatusOutput()->getStatusCode(),
            'eciCode' => $this->getEciCode($paymentDetails),
        ]);
    }

    /**
     * @param $payment
     * @return int|null
     */
    private function getEciCode($payment): ?int
    {
        $cardPaymentMethod = $payment->getPaymentOutput()->getCardPaymentMethodSpecificOutput();
        if (!$cardPaymentMethod) {
            return null;
        }

        return (int) $cardPaymentMethod->getThreeDSecureResults()->getEci();
    }
}
