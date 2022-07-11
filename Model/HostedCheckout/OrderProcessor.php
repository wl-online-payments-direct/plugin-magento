<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\HostedCheckout;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Model\SaleOperationInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Operations\SaleOperation;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use Worldline\Payment\Model\HostedCheckout\Order\EmailSender;
use Worldline\Payment\Model\PaymentProvider;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderProcessor
{
    private const PAYMENT_CREATED = 'PAYMENT_CREATED';
    private const REJECTED = 'REJECTED';
    private const CANCELLED = 'CANCELLED';
    private const REDIRECTED = 'REDIRECTED';

    /**
     * @var SaleOperation
     */
    private $saleOperation;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ResponseProvider
     */
    private $responseProvider;

    /**
     * @var PaymentProvider
     */
    private $paymentProvider;

    /**
     * @var VaultManager
     */
    private $vaultManager;

    /**
     * @var PaymentResponse
     */
    private $paymentResponse;

    /**
     * @var OrderPaymentInterface
     */
    private $orderPayment;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @param SaleOperation $saleOperation
     * @param OrderRepositoryInterface $orderRepository
     * @param ResponseProvider $responseProvider
     * @param PaymentProvider $paymentProvider
     * @param VaultManager $vaultManager
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param EmailSender $emailSender
     */
    public function __construct(
        SaleOperation $saleOperation,
        OrderRepositoryInterface $orderRepository,
        ResponseProvider $responseProvider,
        PaymentProvider $paymentProvider,
        VaultManager $vaultManager,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        EmailSender $emailSender
    ) {
        $this->saleOperation = $saleOperation;
        $this->orderRepository = $orderRepository;
        $this->responseProvider = $responseProvider;
        $this->paymentProvider = $paymentProvider;
        $this->vaultManager = $vaultManager;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->emailSender = $emailSender;
    }

    /**
     * @param string $hostedCheckoutId
     * @param string $returnId
     * @return Order
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function process(string $hostedCheckoutId, string $returnId): Order
    {
        $this->initHostedCheckoutResponse($hostedCheckoutId, $returnId);
        $this->orderPayment = $this->paymentProvider->getPayment($hostedCheckoutId);
        if (!$this->orderPayment || ($this->orderPayment->getAdditionalInformation('RETURNMAC') != $returnId)) {
            throw new LocalizedException(__('Hosted checkout: Return mac has not correct value'));
        }

        if ($this->orderPayment->getOrder()->getState() != Order::STATE_NEW) {
            return $this->orderPayment->getOrder();
        }

        $cardPaymentMethodSpecificOutput = $this->paymentResponse->getPaymentOutput()
            ->getCardPaymentMethodSpecificOutput();

        $this->orderPayment->setLastTransId($this->paymentResponse->getId());
        $this->orderPayment->setCcTransId($this->paymentResponse->getId());
        $this->orderPayment->setTransactionId($this->paymentResponse->getId());
        $this->orderPayment->setCcStatusDescription($this->paymentResponse->getStatus());
        if ($cardPaymentMethodSpecificOutput) {
            $this->orderPayment->setAdditionalInformation(
                'card_number',
                mb_substr($cardPaymentMethodSpecificOutput->getCard()->getCardNumber(), -4)
            );
            $this->orderPayment->setAdditionalInformation(
                'payment_product_id',
                $cardPaymentMethodSpecificOutput->getPaymentProductId()
            );
        }

        $action = $this->orderPayment->getMethodInstance()->getConfigPaymentAction();
        $order = $this->orderPayment->getOrder();

        if (MethodInterface::ACTION_AUTHORIZE === $action) {
            $this->authorize();
        } elseif (MethodInterface::ACTION_AUTHORIZE_CAPTURE === $action) {
            $this->capture();
            foreach ($order->getInvoiceCollection()->getItems() as $invoice) {
                $invoice->pay();
            }
        }

        $this->emailSender->send($order);

        if ($cardPaymentMethodSpecificOutput) {
            $this->vaultManager->setVault($this->orderPayment, $cardPaymentMethodSpecificOutput);
        }

        if (is_array($order->getRelatedObjects())) {
            $this->updateTransaction($order->getRelatedObjects(), $this->paymentResponse->getId());
        }

        $order->setState(Order::STATE_PROCESSING)
            ->setStatus(Order::STATE_PROCESSING);

        $this->orderPaymentRepository->save($this->orderPayment);
        $order->setPayment($this->orderPayment);
        $this->orderRepository->save($order);

        return $this->orderPayment->getOrder();
    }

    /**
     * @param string $hostedCheckoutId
     * @return void
     * @throws LocalizedException
     */
    public function initHostedCheckoutResponse(string $hostedCheckoutId)
    {
        $hostedCheckoutResponse = $this->responseProvider->getHostedCheckoutResponse($hostedCheckoutId);
        if (!$hostedCheckoutResponse || ($hostedCheckoutResponse->getStatus() !== self::PAYMENT_CREATED)) {
            throw new LocalizedException(
                __('Hosted checkout: Absent payment response or status is not payment created')
            );
        }

        $this->paymentResponse = $hostedCheckoutResponse->getCreatedPaymentOutput()->getPayment();
        if (in_array($this->paymentResponse->getStatus(), [self::CANCELLED, self::REJECTED, self::REDIRECTED])) {
            throw new LocalizedException(__('Hosted checkout: Payment status is rejected'));
        }
    }

    /**
     * @return void
     */
    private function authorize()
    {
        $order = $this->orderPayment->getOrder();
        $totalDue = $order->getTotalDue();

        $baseTotalDue = $order->getBaseTotalDue();
        $this->orderPayment->authorize(false, $baseTotalDue);
        $this->orderPayment->setAmountAuthorized($totalDue);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function capture()
    {
        $order = $this->orderPayment->getOrder();

        $totalDue = $order->getTotalDue();
        $baseTotalDue = $order->getBaseTotalDue();

        $this->orderPayment->setAmountAuthorized($totalDue);
        $this->orderPayment->setBaseAmountAuthorized($baseTotalDue);

        if ($this->canSale($this->orderPayment)) {
            $this->saleOperation->execute($this->orderPayment);
        } else {
            $this->orderPayment->capture();
        }
    }

    /**
     * @param OrderPaymentInterface $orderPayment
     * @return bool
     * @throws LocalizedException
     */
    private function canSale(OrderPaymentInterface $orderPayment): bool
    {
        $method = $orderPayment->getMethodInstance();

        return $method instanceof SaleOperationInterface && $method->canSale();
    }

    /**
     * @param array $relatedObjects
     * @param string $transactionId
     * @return void
     */
    private function updateTransaction(array $relatedObjects, string $transactionId)
    {
        foreach ($relatedObjects as $relatedObject) {
            if ($relatedObject instanceof TransactionInterface) {
                $relatedObject->setTxnId($transactionId);
                $relatedObject->setIsClosed(false);
                return;
            }
        }
    }
}
