<?php

declare(strict_types=1);

namespace Worldline\Payment\Controller\Payment;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Worldline\Payment\Model\Order\Service\CreditCardProcessor;

/**
 * Available only for Credit Card
 */
class Result extends Action
{
    public const SUCCESS_URL = 'checkout/onepage/success';
    public const FAIL_URL = 'worldline/payment/failed';

    /**
     * @var CreditCardProcessor
     */
    private $paymentProcessor;

    public function __construct(
        Context $context,
        CreditCardProcessor $paymentProcessor
    ) {
        parent::__construct($context);
        $this->paymentProcessor = $paymentProcessor;
    }

    /**
     * @return ResultInterface
     * @throws Exception
     */
    public function execute(): ResultInterface
    {
        $paymentId = $this->getRequest()->getParam('paymentId');
        $returnId = $this->getRequest()->getParam('RETURNMAC');

        if (!$paymentId || !$returnId) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(self::FAIL_URL);
        }

        if ($this->paymentProcessor->getPaymentReturnMac($paymentId) != $returnId) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(self::FAIL_URL);
        }

        $paymentStatusCode = $this->paymentProcessor->process($paymentId);
        if (!in_array($paymentStatusCode, CreditCardProcessor::SUCCESS_STATUS_CODES)) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(self::FAIL_URL);
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(self::SUCCESS_URL);
    }
}
