<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Service\Creator\Request\Order;

use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use OnlinePayments\Sdk\Domain\AmountOfMoneyFactory;
use OnlinePayments\Sdk\Domain\LineItem;
use OnlinePayments\Sdk\Domain\LineItemFactory;
use OnlinePayments\Sdk\Domain\OrderLineDetailsFactory;
use OnlinePayments\Sdk\Domain\ShoppingCart;
use OnlinePayments\Sdk\Domain\ShoppingCartFactory;

class ShoppingCartDataBuilder
{
    /**
     * @var ShoppingCartFactory
     */
    private $shoppingCartFactory;

    /**
     * @var LineItemFactory
     */
    private $lineItemFactory;

    /**
     * @var AmountOfMoneyFactory
     */
    private $amountOfMoneyFactory;

    /**
     * @var OrderLineDetailsFactory
     */
    private $orderLineDetailsFactory;

    /**
     * @var LineItem[]
     */
    private $lineItems = [];

    /**
     * @var float|int
     */
    private $cartTotal = 0;

    /**
     * @var ShoppingCartDataDebugLogger
     */
    private $shoppingCartDataDebugLogger;

    public function __construct(
        ShoppingCartFactory $shoppingCartFactory,
        LineItemFactory $lineItemFactory,
        AmountOfMoneyFactory $amountOfMoneyFactory,
        OrderLineDetailsFactory $orderLineDetailsFactory,
        ShoppingCartDataDebugLogger $shoppingCartDataDebugLogger
    ) {
        $this->shoppingCartFactory = $shoppingCartFactory;
        $this->lineItemFactory = $lineItemFactory;
        $this->amountOfMoneyFactory = $amountOfMoneyFactory;
        $this->orderLineDetailsFactory = $orderLineDetailsFactory;
        $this->shoppingCartDataDebugLogger = $shoppingCartDataDebugLogger;
    }

    public function build(CartInterface $quote): ?ShoppingCart
    {
        $this->lineItems = [];
        $this->cartTotal = 0;

        foreach ($quote->getItems() as $item) {
            $this->addLineItem($quote, $item);
        }

        $this->addShippingLineItem($quote);

        $shoppingCart = $this->shoppingCartFactory->create();
        $shoppingCart->setItems($this->lineItems);

        if ($this->skipLineItems($quote)) {
            $this->shoppingCartDataDebugLogger->log($quote, $shoppingCart);
            return null;
        }

        return $shoppingCart;
    }

    public function addLineItem(CartInterface $quote, CartItemInterface $item): void
    {
        if ($item->getParentItem()) {
            return;
        }

        $lineItem = $this->lineItemFactory->create();

        $orderLineDetails = $this->orderLineDetailsFactory->create();
        $orderLineDetails->setDiscountAmount($this->preparePrice(
            (float) ($this->getDiscountAmount($item) / $item->getQty())
        ));
        $orderLineDetails->setProductCode($item->getSku());
        $orderLineDetails->setProductName($item->getName());
        $orderLineDetails->setProductPrice($this->preparePrice((float) $item->getPrice()));
        $orderLineDetails->setProductType($item->getProductType());
        $orderLineDetails->setQuantity((float) $item->getQty());
        $orderLineDetails->setTaxAmount($this->preparePrice((float) ($item->getTaxAmount() / $item->getQty())));
        $lineItem->setOrderLineDetails($orderLineDetails);

        $amountOfMoney = $this->amountOfMoneyFactory->create();
        $amountOfMoney->setCurrencyCode($quote->getCurrency()->getQuoteCurrencyCode());

        $totalAmount = (
            $orderLineDetails->getProductPrice()
            + $orderLineDetails->getTaxAmount()
            - $orderLineDetails->getDiscountAmount()
        ) * $item->getQty();

        $this->cartTotal += $totalAmount;

        $amountOfMoney->setAmount($totalAmount);
        $lineItem->setAmountOfMoney($amountOfMoney);

        $this->lineItems[] = $lineItem;
    }

    public function addShippingLineItem(CartInterface $quote): void
    {
        $shippingAmount = $this->preparePrice((float) $quote->getShippingAddress()->getShippingAmount());
        $lineItem = $this->lineItemFactory->create();

        $amountOfMoney = $this->amountOfMoneyFactory->create();
        $amountOfMoney->setCurrencyCode($quote->getCurrency()->getQuoteCurrencyCode());
        $amountOfMoney->setAmount($shippingAmount);
        $lineItem->setAmountOfMoney($amountOfMoney);

        $orderLineDetails = $this->orderLineDetailsFactory->create();
        $orderLineDetails->setProductName(__('Shipping'));
        $orderLineDetails->setQuantity(1);
        $orderLineDetails->setProductPrice($shippingAmount);
        $lineItem->setOrderLineDetails($orderLineDetails);

        $this->lineItems[] = $lineItem;
    }

    public function preparePrice(float $price): int
    {
        return (int) round($price * 100);
    }

    private function getDiscountAmount(CartItemInterface $item): float
    {
        if ($item->getProductType() === BundleProductType::TYPE_CODE) {
            $discountAmount = 0;

            foreach ($item->getChildren() as $child) {
                $discountAmount += $child->getDiscountAmount();
            }

            return (float) $discountAmount;
        }

        return (float) $item->getDiscountAmount();
    }

    private function skipLineItems(CartInterface $quote): bool
    {
        $shippingAmount = $this->preparePrice((float) $quote->getShippingAddress()->getShippingAmount());
        $cartGrandTotal = $this->preparePrice((float) $quote->getGrandTotal());

        return (bool) ($cartGrandTotal - $this->cartTotal - $shippingAmount);
    }
}
