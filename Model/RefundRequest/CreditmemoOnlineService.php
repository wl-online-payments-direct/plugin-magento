<?php
declare(strict_types=1);

namespace Worldline\Payment\Model\RefundRequest;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Worldline\Payment\Api\Data\RefundRequestInterfaceFactory;
use Worldline\Payment\Api\RefundRequestRepositoryInterface;

/**
 * @core
 */
class CreditmemoOnlineService
{
    /**
     * @var RefundRequestRepositoryInterface
     */
    private $refundRequestRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var RefundRequestInterfaceFactory
     */
    private $refundRequestFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        RefundRequestRepositoryInterface $refundRequestRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        RefundRequestInterfaceFactory $refundRequestFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->refundRequestRepository = $refundRequestRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->refundRequestFactory = $refundRequestFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     * @return CreditmemoInterface
     * @throws LocalizedException
     */
    public function refund(CreditmemoInterface $creditmemo): CreditmemoInterface
    {
        try {
            $order = $creditmemo->getOrder();
            $invoice = $creditmemo->getInvoice();
            $invoiceId = (int)$invoice->getId();
            $payment = $order->getPayment();
            $baseAmountToRefund = $payment->formatAmount($creditmemo->getBaseGrandTotal());
            $gateway = $payment->getMethodInstance();
            $gateway->setStore($order->getStoreId());
            $gateway->refund($payment, $baseAmountToRefund);

            $payment->addTransaction(TransactionInterface::TYPE_REFUND, $creditmemo, true);

            $creditmemo->setState(Creditmemo::STATE_OPEN);
            $this->creditmemoRepository->save($creditmemo);

            $amount = (int) round($creditmemo->getGrandTotal() * 100);
            $this->saveRefundRequest($invoiceId, $order->getIncrementId(), (int)$creditmemo->getId(), $amount);
            $this->orderRepository->save($order); //need to save $order->getCustomerNoteNotify() flag changes
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return $creditmemo;
    }

    private function saveRefundRequest(
        int $invoiceId,
        string $incrementId,
        int $creditMemoId,
        int $amount
    ): void {
        $refundRequest = $this->refundRequestFactory->create();

        $refundRequest->setInvoiceId($invoiceId);
        $refundRequest->setIncrementId($incrementId);
        $refundRequest->setCreditMemoId($creditMemoId);
        $refundRequest->setAmount($amount);

        $this->refundRequestRepository->save($refundRequest);
    }
}
