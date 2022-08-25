<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Transaction;

use Magento\Sales\Api\Data\OrderInterface;
use Worldline\Payment\Api\Data\PaymentInfoInterface;
use Worldline\Payment\Api\Data\PaymentInfoInterfaceFactory;
use Worldline\Payment\Api\TransactionRepositoryInterface;
use Worldline\Payment\Api\Data\TransactionInterface;

class PaymentInfoBuilder
{
    /**
     * @var PaymentInfoInterfaceFactory
     */
    private $paymentInfoFactory;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(
        PaymentInfoInterfaceFactory $paymentInfoFactory,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->paymentInfoFactory = $paymentInfoFactory;
        $this->transactionRepository = $transactionRepository;
    }

    public function build(OrderInterface $order): PaymentInfoInterface
    {
        /** @var PaymentInfoInterface $paymentInfo */
        $paymentInfo = $this->paymentInfoFactory->create();

        $incrementId = (string)$order->getIncrementId();
        $lastTransaction = $this->transactionRepository->getLastTransaction($incrementId);
        $paymentInfo = $this->setStatusInfo($paymentInfo, $lastTransaction);

        $authorizeTransaction = $this->transactionRepository->getAuthorizeTransaction($incrementId);

        $paymentInfo = $this->setInfoByAuthorizeTransaction($paymentInfo, $authorizeTransaction);
        $paymentInfo = $this->calculateTransactionAmounts($paymentInfo, $authorizeTransaction, $incrementId);

        return $paymentInfo;
    }

    private function setInfoByAuthorizeTransaction(
        PaymentInfoInterface $paymentInfo,
        ?TransactionInterface $authorizeTransaction
    ): PaymentInfoInterface {
        if (!$authorizeTransaction) {
            return $paymentInfo;
        }

        $paymentInfo->setAuthorizedAmount($authorizeTransaction->getAmount());
        $paymentInfo->setFraudResult(
            $authorizeTransaction->getAdditionalData()[TransactionInterface::FRAUD_RESULT] ?? ''
        );
        $paymentInfo->setPaymentMethod(
            $authorizeTransaction->getAdditionalData()[TransactionInterface::PAYMENT_METHOD] ?? ''
        );
        $paymentInfo->setCardLastNumbers(
            $authorizeTransaction->getAdditionalData()[TransactionInterface::CARD_LAST_4] ?? ''
        );
        $paymentInfo->setPaymentProductId(
            $authorizeTransaction->getAdditionalData()[TransactionInterface::PAYMENT_PRODUCT_ID] ?? 0
        );
        $paymentInfo->setCurrency($authorizeTransaction->getCurrency());

        return $paymentInfo;
    }

    private function calculateTransactionAmounts(
        PaymentInfoInterface $paymentInfo,
        ?TransactionInterface $authorizeTransaction,
        string $incrementId
    ): PaymentInfoInterface {
        $captureTransaction = $this->transactionRepository->getCaptureTransaction($incrementId);
        if ($captureTransaction) {
            $amountAvailableForCapture = $authorizeTransaction->getAmount() - $captureTransaction->getAmount();
            $paymentInfo->setAmountAvailableForCapture($amountAvailableForCapture);
        }

        $refundTransactions = $this->transactionRepository->getRefundedTransactions($incrementId);
        $refundAmount = 0;
        if ($refundTransactions) {
            foreach ($refundTransactions as $refundTransaction) {
                $refundAmount += $refundTransaction->getAmount();
            }

            $paymentInfo->setRefundedAmount($refundAmount);
        }

        $pendingRefundTransactions = $this->transactionRepository->getPendingRefundTransactions($incrementId);
        $pendingRefundAmount = 0;
        if ($pendingRefundTransactions) {
            foreach ($pendingRefundTransactions as $pendingRefundTransaction) {
                $pendingRefundAmount += $pendingRefundTransaction->getAmount();
            }
        }

        if ($captureTransaction) {
            $amountAvailableForRefund = $captureTransaction->getAmount() - $pendingRefundAmount - $refundAmount;
            // TODO: implement for future
            // $paymentInfo->setAmountAvailableForRefund($amountAvailableForRefund);

            if ($amountAvailableForRefund > 0) {
                $paymentInfo = $this->setStatusInfo($paymentInfo, $captureTransaction);
            } elseif (!empty($refundTransactions)) {
                $lastRefundTransaction = current($refundTransactions);
                $paymentInfo = $this->setStatusInfo($paymentInfo, $lastRefundTransaction);
            }
        }

        return $paymentInfo;
    }

    private function setStatusInfo(
        PaymentInfoInterface $paymentInfo,
        ?TransactionInterface $transaction
    ): PaymentInfoInterface {
        if ($transaction) {
            $paymentInfo->setStatus($transaction->getStatus());
            $paymentInfo->setStatusCode($transaction->getStatusCode());
            $paymentInfo->setLastTransactionNumber($transaction->getTransactionId());
        }

        return $paymentInfo;
    }
}
