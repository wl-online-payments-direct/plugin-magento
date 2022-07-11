<?php

declare(strict_types=1);

namespace Worldline\Payment\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Model\Config\OrderStatusUpdater;
use Worldline\Payment\Model\HostedCheckout\OrderCanceler;
use Worldline\Payment\Model\HostedCheckout\OrderProcessor;

class Hosted extends Action
{
    private const SUCCESS_URL = 'checkout/onepage/success';
    private const FAIL_URL = 'worldline/payment/failed';

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var OrderProcessor
     */
    private $orderProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderStatusUpdater
     */
    private $orderStatusUpdater;

    /**
     * @var OrderCanceler
     */
    private $orderCanceler;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param OrderProcessor $orderProcessor
     * @param LoggerInterface $logger
     * @param OrderStatusUpdater $orderStatusUpdater
     * @param OrderCanceler $orderCanceler
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        OrderProcessor $orderProcessor,
        LoggerInterface $logger,
        OrderStatusUpdater $orderStatusUpdater,
        OrderCanceler $orderCanceler
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->orderProcessor = $orderProcessor;
        $this->logger = $logger;
        $this->orderStatusUpdater = $orderStatusUpdater;
        $this->orderCanceler = $orderCanceler;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $hostedCheckoutId = (string) $this->getRequest()->getParam('hostedCheckoutId');
        $returnId = (string) $this->getRequest()->getParam('RETURNMAC');

        if (!$hostedCheckoutId || !$returnId) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(self::FAIL_URL);
        }

        try {
            if (!$this->orderStatusUpdater->isReceivingWebhooksAllowed()) {
                $this->orderProcessor->process($hostedCheckoutId, $returnId);
            } else {
                $this->orderProcessor->initHostedCheckoutResponse($hostedCheckoutId, $returnId);
            }

            $quote = $this->checkoutSession->getQuote();
            $quote->removeAllItems();
            $this->quoteRepository->save($quote);

            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(self::SUCCESS_URL);
        } catch (LocalizedException $exception) {
            $this->orderCanceler->cancelByWorldlinePaymentId($hostedCheckoutId);
            $this->logger->warning($exception->getMessage());
            $this->checkoutSession->restoreQuote();
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(self::FAIL_URL);
        }
    }
}
