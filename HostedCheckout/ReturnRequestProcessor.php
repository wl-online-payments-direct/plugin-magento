<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;
use Worldline\Payment\HostedCheckout\Service\Getter\Request;
use Worldline\Payment\Model\Config\OrderStatusUpdater;
use Worldline\Payment\Model\Order\PendingOrderException;
use Worldline\Payment\Model\ResourceModel\Quote as QuoteResource;

class ReturnRequestProcessor
{
    private const CANCELLED_BY_CONSUMER_STATUS = 'CANCELLED_BY_CONSUMER';
    private const REDIRECTED_STATUS = 'REDIRECTED';

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderStatusUpdater
     */
    private $orderStatusUpdater;

    /**
     * @var Request
     */
    private $getRequest;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    public function __construct(
        Session $checkoutSession,
        LoggerInterface $logger,
        OrderStatusUpdater $orderStatusUpdater,
        Request $getRequest,
        QuoteResource $quoteResource,
        QuoteManagement $quoteManagement,
        OrderFactory $orderFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->orderStatusUpdater = $orderStatusUpdater;
        $this->getRequest = $getRequest;
        $this->quoteResource = $quoteResource;
        $this->quoteManagement = $quoteManagement;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @param string $hostedCheckoutId
     * @param string $returnId
     * @return string|null
     *
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    public function processRequest(string $hostedCheckoutId, string $returnId): ?string
    {
        if (!$hostedCheckoutId || !$returnId) {
            throw new LocalizedException(__('Invalid request'));
        }

        try {
            $request = $this->getRequest->create($hostedCheckoutId);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new LocalizedException(__('The payment has failed, please, try again'));
        }

        if (self::CANCELLED_BY_CONSUMER_STATUS === $request->getStatus()
            || self:: REDIRECTED_STATUS === $request->getCreatedPaymentOutput()->getPayment()->getStatus()) {
            throw new LocalizedException(__('Cancelled by consumer'));
        }

        $quote = $this->quoteResource->getQuoteByWorldlinePaymentId($hostedCheckoutId);
        if ($quote->getPayment()->getAdditionalInformation('return_id') !== $returnId) {
            throw new LocalizedException(__('Wrong return id'));
        }

        $order = $this->orderFactory->create()->loadByIncrementId($quote->getReservedOrderId());
        if (!$order->getId()) {
            $quote->setIsActive(false);
            $this->quoteResource->saveCart($quote);
            $this->checkoutSession->clearStorage();
            throw new PendingOrderException(
                __(
                    'Thank you for your order %1.'
                    . ' Your order is still being processed and you will receive a confirmation e-mail.'
                    . ' Please contact us in case you don\'t receive the confirmation within 10 minutes.',
                    $quote->getReservedOrderId()
                )
            );
        }

        $orderId = $this->checkoutSession->getLastRealOrder()->getEntityId();
        $this->checkoutSession->setLastOrderId($orderId);
        $this->checkoutSession->setLastRealOrderId($quote->getReservedOrderId());
        $this->checkoutSession->setLastQuoteId($quote->getId());
        $this->checkoutSession->setLastSuccessQuoteId($quote->getId());

        return (string) $quote->getReservedOrderId();
    }
}
