<?php

declare(strict_types=1);

namespace Worldline\Payment\Controller\Payment;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;

class Redirect extends Action
{
    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $orderPaymentRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return ResultInterface
     * @throws LocalizedException
     * @throws InputException
     */
    public function execute(): ResultInterface
    {
        if (!$this->getRequest()->isAjax()) {
            throw new LocalizedException(__('Not AJAX'));
        }

        $result = $this->resultJsonFactory->create();
        return $result->setData($this->getRedirectData());
    }

    /**
     * @return array
     * @throws InputException
     */
    private function getRedirectData(): array
    {
        $orderId = (int) $this->getRequest()->getParam('id');
        if (!$orderId) {
            return [];
        }

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('parent_id', $orderId)->create();
        $payment = $this->orderPaymentRepository->getList($searchCriteria)->getFirstItem();

        if (!$payment || ($payment->getCcStatusDescription() !== 'REDIRECTED')) {
            return [];
        }

        return ['url' => $payment->getAdditionalInformation('redirectURL')];
    }
}
