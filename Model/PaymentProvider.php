<?php

declare(strict_types=1);

namespace Worldline\Payment\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;

class PaymentProvider
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderPaymentRepositoryInterface $orderPaymentRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderPaymentRepository = $orderPaymentRepository;
    }

    /**
     * @param string $worldlinePaymentId
     * @return OrderPaymentInterface|null
     */
    public function getPayment(string $worldlinePaymentId): ?OrderPaymentInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('worldline_payment_id', $worldlinePaymentId)
            ->create();
        $payment = $this->orderPaymentRepository->getList($searchCriteria)->getFirstItem();

        if (!$payment->getId()) {
            return null;
        }

        return $payment;
    }
}
