<?php

declare(strict_types=1);

namespace Worldline\Payment\Controller\CreditCard;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\OrderFactory;
use Worldline\Payment\Model\ResourceModel\Quote as QuoteResource;

class ReturnThreeDSecure extends Action
{
    public const SUCCESS_URL = 'checkout/onepage/success';
    public const FAIL_URL = 'worldline/CreditCard/failed';

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    public function __construct(
        Context $context,
        QuoteResource $quoteResource,
        Session $checkoutSession,
        OrderFactory $orderFactory
    ) {
        parent::__construct($context);
        $this->quoteResource = $quoteResource;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
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

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        sleep(2); // wait for the webhook

        $quote = $this->quoteResource->getQuoteByWorldlinePaymentId($returnId);
        $order = $this->orderFactory->create()->loadByIncrementId($quote->getReservedOrderId());

        if (!$order->getId()) {
            $this->messageManager->addSuccessMessage(__(
                'Thank you for your order %1.'
                . ' Your order is still being processed and you will receive a confirmation e-mail.'
                . ' Please contact us in case you don\'t receive the confirmation within 10 minutes.',
                $quote->getReservedOrderId()
            ));
            $this->checkoutSession->clearQuote();
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(self::FAIL_URL);
        }

        if ($order->getPayment()->getAdditionalInformation('RETURNMAC') != $returnId) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(self::FAIL_URL);
        }

        $this->checkoutSession->setLastRealOrderId($quote->getReservedOrderId());
        $this->checkoutSession->setLastOrderId($order->getId());
        $this->checkoutSession->setLastQuoteId($quote->getId());
        $this->checkoutSession->setLastSuccessQuoteId($quote->getId());

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(self::SUCCESS_URL);
    }
}
