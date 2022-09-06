<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Transaction;

use Worldline\Payment\Api\Data\TransactionInterface;
use Worldline\Payment\Api\TransactionRepositoryInterface;
use Worldline\Payment\Model\Transaction\ResourceModel\Transaction as TransactionResource;
use Worldline\Payment\Model\Transaction\ResourceModel\Transaction\CollectionFactory;

class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @var TransactionResource
     */
    private $transactionResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $transactions = [];

    public function __construct(TransactionResource $transactionResource, CollectionFactory $collectionFactory)
    {
        $this->transactionResource = $transactionResource;
        $this->collectionFactory = $collectionFactory;
    }

    public function save(TransactionInterface $refundRequest): TransactionInterface
    {
        $this->transactionResource->save($refundRequest);
        return $refundRequest;
    }

    public function getLastTransaction(string $incrementId): ?TransactionInterface
    {
        $transactions = $this->getAllTransactions($incrementId);
        if (!$transactions) {
            return null;
        }

        return current($transactions);
    }

    /**
     * If only "Authorize" is done, a transaction with status code
     * TransactionStatusInterface::PENDING_CAPTURE_CODE is made.
     *
     * If "Authorize and capture" is done: a transaction with status code
     * TransactionStatusInterface::CAPTURED_CODE is created
     *
     * @param string $incrementId
     * @return TransactionInterface|null
     */
    public function getAuthorizeTransaction(string $incrementId): ?TransactionInterface
    {
        if (!$transactions = $this->getAllTransactions($incrementId)) {
            return null;
        }

        foreach ($transactions as $transaction) {
            if ($transaction->getStatusCode() == TransactionStatusInterface::PENDING_CAPTURE_CODE) {
                return $transaction;
            }
        }

        return $this->getCaptureTransaction($incrementId);
    }

    public function getCaptureTransaction(string $incrementId): ?TransactionInterface
    {
        $result = null;
        if (!$transactions = $this->getAllTransactions($incrementId)) {
            return null;
        }

        foreach ($transactions as $transaction) {
            if ($transaction->getStatusCode() == TransactionStatusInterface::CAPTURED_CODE) {
                $result = $transaction;
                break;
            }
        }

        return $result;
    }

    public function getRefundedTransactions(string $incrementId): array
    {
        $transactions = $this->getAllTransactions($incrementId);
        $result = [];
        if (!$transactions) {
            return $result;
        }

        foreach ($transactions as $transaction) {
            if ($transaction->getStatusCode() == TransactionStatusInterface::REFUNDED_CODE) {
                $result[$transaction->getTransactionId()] = $transaction;
            }
        }

        return $result;
    }

    public function getPendingRefundTransactions(string $incrementId): array
    {
        $transactions = $this->getAllTransactions($incrementId);
        $result = [];
        if (!$transactions) {
            return $result;
        }

        $refundedTransaction = $this->getRefundedTransactions($incrementId);

        foreach ($transactions as $transaction) {
            if ($transaction->getStatusCode() == TransactionStatusInterface::PENDING_REFUND_CODE
                && !in_array($transaction->getTransactionId(), array_keys($refundedTransaction))
            ) {
                $result[$transaction->getTransactionId()] = $transaction;
            }
        }

        return $result;
    }

    private function getAllTransactions(string $incrementId): array
    {
        if (!isset($this->transactions[$incrementId])) {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(TransactionInterface::INCREMENT_ID, ['eq' => $incrementId]);
            $collection->getSelect()->order('main_table.entity_id DESC');

            $this->transactions[$incrementId] = $collection->getItems();
        }

        return $this->transactions[$incrementId];
    }
}
