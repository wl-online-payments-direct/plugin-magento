<?php
declare(strict_types=1);

namespace Worldline\Payment\Model\Order\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\Config\WorldlineConfig;
use Worldline\Payment\Model\PaymentProvider;

class CreditCardProcessor
{
    public const FAIL_STATUS_CODE = 0;
    public const SUCCESS_STATUS_CODES = [5, 9];

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var ClientProvider
     */
    private $clientProvider;

    /**
     * @var PaymentProvider
     */
    private $paymentProvider;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    /**
     * @var OrderPaymentInterface|null
     */
    private $payment;

    /**
     * @param WorldlineConfig $worldlineConfig
     * @param ClientProvider $clientProvider
     * @param PaymentProvider $paymentProvider
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     */
    public function __construct(
        WorldlineConfig $worldlineConfig,
        ClientProvider $clientProvider,
        PaymentProvider $paymentProvider,
        OrderRepositoryInterface $orderRepository,
        OrderPaymentRepositoryInterface $orderPaymentRepository
    ) {
        $this->worldlineConfig = $worldlineConfig;
        $this->clientProvider = $clientProvider;
        $this->paymentProvider = $paymentProvider;
        $this->orderRepository = $orderRepository;
        $this->orderPaymentRepository = $orderPaymentRepository;
    }

    /**
     * @param string $paymentId
     * @return int
     * @throws LocalizedException
     */
    public function process(string $paymentId): int
    {
        $payment = $this->getPayment($paymentId);
        if (!$payment) {
            return self::FAIL_STATUS_CODE;
        }

        $paymentStatusCode = $this->getPaymentStatusCode($paymentId);
        if (in_array($paymentStatusCode, self::SUCCESS_STATUS_CODES)) {
            $payment->setIsTransactionApproved(true);
        } else {
            $payment->setIsTransactionDenied(true);
        }

        $payment->update();
        $this->orderPaymentRepository->save($payment);
        $this->orderRepository->save($payment->getOrder());

        return $paymentStatusCode;
    }

    /**
     * @param string $paymentId
     * @return string|null
     */
    public function getPaymentReturnMac(string $paymentId): ?string
    {
        $payment = $this->getPayment($paymentId);
        if (!$payment) {
            return null;
        }

        return (string)$payment->getAdditionalInformation('RETURNMAC');
    }

    /**
     * @param string $paymentId
     * @return string|null
     */
    public function getIncrementId(string $paymentId): ?string
    {
        $payment = $this->getPayment($paymentId);
        if (!$payment) {
            return null;
        }

        return (string)$payment->getOrder()->getIncrementId();
    }

    /**
     * @param string $paymentId
     * @return OrderPaymentInterface|null
     */
    private function getPayment(string $paymentId): ?OrderPaymentInterface
    {
        if (!$this->payment) {
            $this->payment = $this->paymentProvider->getPayment($paymentId);
        }

        return $this->payment;
    }

    /**
     * @param string $paymentId
     * @return int
     * @throws \Exception
     */
    private function getPaymentStatusCode(string $paymentId): int
    {
        $worldlinePayment = $this->clientProvider->getClient()
            ->merchant($this->worldlineConfig->getMerchantId())
            ->payments()
            ->getPayment($paymentId);

        return (int) $worldlinePayment->getStatusOutput()->getStatusCode();
    }
}
