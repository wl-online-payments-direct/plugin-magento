<?php

declare(strict_types=1);

namespace Worldline\Payment\Controller\CreditCard;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Worldline\Payment\CreditCard\ReturnRequestProcessor;
use Worldline\Payment\Model\Order\PendingOrderException;

class ReturnUrl extends Action
{
    public const SUCCESS_URL = '/checkout/onepage/success';
    public const FAIL_URL = '/worldline/CreditCard/failed';

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

    /**
     * @return ResultInterface
     * @throws Exception
     */
    public function execute(): ResultInterface
    {
        $hostedTokenizationId = $this->getRequest()->getParam('hosted_tokenization_id');
        if (!$hostedTokenizationId) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(
                ['url' => self::FAIL_URL]
            );
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        sleep(2); // wait for the webhook

        try {
            $result = $this->returnRequestProcessor->processRequest($hostedTokenizationId);
        } catch (PendingOrderException $e) {
            $result = true;
            $this->messageManager->addSuccessMessage($e->getMessage());
        }

        if (!$result) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(
                ['url' => self::FAIL_URL]
            );
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(
            ['url' => self::SUCCESS_URL]
        );
    }
}
