<?php

declare(strict_types=1);

namespace Worldline\Payment\Controller\HostedCheckout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Worldline\Payment\HostedCheckout\ReturnRequestProcessor;
use Worldline\Payment\Model\Order\PendingOrderException;

class ReturnUrl extends Action
{
    private const SUCCESS_URL = 'checkout/onepage/success';
    private const FAIL_URL = 'worldline/payment/failed';

    /**
     * @var ReturnRequestProcessor
     */
    private $returnRequestProcessor;

    public function __construct(
        Context $context,
        ReturnRequestProcessor $returnRequestProcessor
    ) {
        parent::__construct($context);
        $this->returnRequestProcessor = $returnRequestProcessor;
    }

    public function execute(): ResultInterface
    {
        try {
            $hostedCheckoutId = (string) $this->getRequest()->getParam('hostedCheckoutId');
            $returnId = (string) $this->getRequest()->getParam('RETURNMAC');

            $this->returnRequestProcessor->processRequest($hostedCheckoutId, $returnId);

            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(self::SUCCESS_URL);
        } catch (PendingOrderException $exception) {
            $this->messageManager->addSuccessMessage($exception->getMessage());
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(self::SUCCESS_URL);
        } catch (LocalizedException $exception) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(self::FAIL_URL);
        }
    }
}
