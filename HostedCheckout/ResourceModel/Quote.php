<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\ResourceModel;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory;

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
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');

        return $quote;
    }

    public function getQuoteByHostedCheckoutId(string $hostedCheckoutId): CartInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('additional_information', ['like' => '%' . $hostedCheckoutId . '%']);
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
