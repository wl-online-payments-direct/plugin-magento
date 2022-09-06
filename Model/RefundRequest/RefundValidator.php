<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\RefundRequest;

use Magento\Sales\Model\Order;
use Worldline\Payment\Api\RefundRequestRepositoryInterface;

/**
 * @core
 */
class RefundValidator
{
    /**
     * @var RefundRequestRepositoryInterface
     */
    private $refundRequestRepository;

    /**
     * @var bool|null
     */
    private $result;

    public function __construct(RefundRequestRepositoryInterface $refundRequestRepository)
    {
        $this->refundRequestRepository = $refundRequestRepository;
    }

    public function canRefund(Order $order): bool
    {
        if ($this->result === null) {
            $this->result = $this->getResult($order);
        }

        return $this->result;
    }

    private function getResult(Order $order): bool
    {
        $incrementId = $order->getIncrementId();
        $refundRequests  = $this->refundRequestRepository->getListByIncrementId($incrementId);
        if (!$refundRequests) {
            return true;
        }

        $refundAmount = 0;
        foreach ($refundRequests as $refundRequest) {
            $refundAmount += $refundRequest->getAmount();
        }

        $orderAmount = (int) round($order->getGrandTotal() * 100);
        if ($orderAmount === $refundAmount) {
            return false;
        }

        return true;
    }
}
