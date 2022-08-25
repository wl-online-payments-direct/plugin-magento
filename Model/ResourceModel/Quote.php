<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\ResourceModel;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory;

/**
 * @core
 */
class Quote
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $quotes = [];

    public function __construct(
        QuoteFactory $quoteFactory,
        QuoteResource $quoteResource,
        CollectionFactory $collectionFactory
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteResource = $quoteResource;
        $this->collectionFactory = $collectionFactory;
    }

    public function getQuoteByReservedOrderId(string $reservedOrderId): CartInterface
    {
        if (empty($this->quotes[$reservedOrderId])) {
            $quote = $this->quoteFactory->create();
            $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');
            $this->quotes[$reservedOrderId] = $quote;
        }

        return $this->quotes[$reservedOrderId];
    }

    public function getQuoteByWorldlinePaymentId(string $paymentId): CartInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('additional_information', ['like' => '%' . $paymentId . '%']);
        $collection->setOrder('payment_id');
        $collection->getSelect()->limit(1);
        $quotePayment = $collection->getFirstItem();

        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $quotePayment->getQuoteId());

        return $quote;
    }

    public function saveCart(CartInterface $cart)
    {
        $this->quoteResource->save($cart);
    }
}
