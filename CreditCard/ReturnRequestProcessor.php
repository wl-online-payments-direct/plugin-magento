<?php
declare(strict_types=1);

namespace Worldline\Payment\CreditCard;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Worldline\Payment\Model\Order\PendingOrderException;
use Worldline\Payment\Model\ResourceModel\Quote as QuoteResource;

class ReturnRequestProcessor
{
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
        QuoteResource $quoteResource,
        Session $checkoutSession,
        OrderFactory $orderFactory
    ) {
        $this->quoteResource = $quoteResource;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @throws PendingOrderException
     */
    public function processRequest(string $hostedTokenizationId): ?string
    {
        $quote = $this->quoteResource->getQuoteByWorldlinePaymentId($hostedTokenizationId);
        $order = $this->orderFactory->create()->loadByIncrementId($quote->getReservedOrderId());
        if (!$order->getId()) {
            $quote->setIsActive(false);
            $this->quoteResource->saveCart($quote);
            $this->checkoutSession->clearQuote();
            throw new PendingOrderException(
                __(
                    'Thank you for your order %1.'
                    . ' Your order is still being processed and you will receive a confirmation e-mail.'
                    . ' Please contact us in case you don\'t receive the confirmation within 10 minutes.',
                    $quote->getReservedOrderId()
                )
            );
        }

        $this->checkoutSession->setLastRealOrderId($quote->getReservedOrderId());
        $this->checkoutSession->setLastOrderId($order->getId());
        $this->checkoutSession->setLastQuoteId($quote->getId());
        $this->checkoutSession->setLastSuccessQuoteId($quote->getId());

        return (string) $quote->getReservedOrderId();
    }
}
