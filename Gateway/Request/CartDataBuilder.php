<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Request;

use Magento\Bundle\Model\Product\Type;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use OnlinePayments\Sdk\Domain\AmountOfMoneyFactory;
use OnlinePayments\Sdk\Domain\LineItem;
use OnlinePayments\Sdk\Domain\LineItemFactory;
use OnlinePayments\Sdk\Domain\OrderLineDetailsFactory;
use OnlinePayments\Sdk\Domain\ShoppingCartFactory;
use Worldline\Payment\Gateway\SubjectReader;

class CartDataBuilder implements BuilderInterface
{
    public const CART = 'cart';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

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
     * @var Logger
     */
    private $logger;

    /**
     * @var LineItem[]
     */
    private $lineItems = [];

    /**
     * @var float|int
     */
    private $cartTotal = 0;

    /**
     * @param SubjectReader $subjectReader
     * @param ShoppingCartFactory $shoppingCartFactory
     * @param LineItemFactory $lineItemFactory
     * @param AmountOfMoneyFactory $amountOfMoneyFactory
     * @param OrderLineDetailsFactory $orderLineDetailsFactory
     * @param Logger $logger
     */
    public function __construct(
        SubjectReader $subjectReader,
        ShoppingCartFactory $shoppingCartFactory,
        LineItemFactory $lineItemFactory,
        AmountOfMoneyFactory $amountOfMoneyFactory,
        OrderLineDetailsFactory $orderLineDetailsFactory,
        Logger $logger
    ) {
        $this->subjectReader = $subjectReader;
        $this->shoppingCartFactory = $shoppingCartFactory;
        $this->lineItemFactory = $lineItemFactory;
        $this->amountOfMoneyFactory = $amountOfMoneyFactory;
        $this->orderLineDetailsFactory = $orderLineDetailsFactory;
        $this->logger = $logger;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $this->lineItems = [];
        $this->cartTotal = 0;

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getPayment()->getOrder();

        foreach ($order->getItems() as $item) {
            $this->addLineItem($order, $item);
        }

        $this->addShippingLineItem($order);

        $shoppingCart = $this->shoppingCartFactory->create();
        $shoppingCart->setItems($this->lineItems);

        if ($this->skipLineItems($order, $buildSubject)) {
            $this->logger->debug(['shoppingCart' => $shoppingCart->toJson()]);
            return [
                self::CART => null
            ];
        }

        return [
            self::CART => $shoppingCart
        ];
    }

    /**
     * @param OrderInterface $order
     * @param OrderItemInterface $item
     * @return void
     */
    public function addLineItem(OrderInterface $order, OrderItemInterface $item): void
    {
        if ($item->getParentItem()) {
            return;
        }

        $lineItem = $this->lineItemFactory->create();

        $orderLineDetails = $this->orderLineDetailsFactory->create();
        $orderLineDetails->setDiscountAmount($this->preparePrice(
            (float) ($this->getDiscountAmount($item) / $item->getQtyOrdered())
        ));
        $orderLineDetails->setProductCode($item->getSku());
        $orderLineDetails->setProductName($item->getName());
        $orderLineDetails->setProductPrice($this->preparePrice((float) $item->getPrice()));
        $orderLineDetails->setProductType($item->getProductType());
        $orderLineDetails->setQuantity((float) $item->getQtyOrdered());
        $orderLineDetails->setTaxAmount($this->preparePrice((float) ($item->getTaxAmount() / $item->getQtyOrdered())));
        $lineItem->setOrderLineDetails($orderLineDetails);

        $amountOfMoney = $this->amountOfMoneyFactory->create();
        $amountOfMoney->setCurrencyCode($order->getOrderCurrencyCode());

        $totalAmount = (
            $orderLineDetails->getProductPrice()
            + $orderLineDetails->getTaxAmount()
            - $orderLineDetails->getDiscountAmount()
        ) * $item->getQtyOrdered();

        $this->cartTotal += $totalAmount;

        $amountOfMoney->setAmount($totalAmount);
        $lineItem->setAmountOfMoney($amountOfMoney);

        $this->lineItems[] = $lineItem;
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    public function addShippingLineItem(OrderInterface $order): void
    {
        $shippingAmount = $this->preparePrice((float) $order->getShippingAmount());
        $lineItem = $this->lineItemFactory->create();

        $amountOfMoney = $this->amountOfMoneyFactory->create();
        $amountOfMoney->setCurrencyCode($order->getOrderCurrencyCode());
        $amountOfMoney->setAmount($shippingAmount);
        $lineItem->setAmountOfMoney($amountOfMoney);

        $orderLineDetails = $this->orderLineDetailsFactory->create();
        $orderLineDetails->setProductName(__('Shipping'));
        $orderLineDetails->setQuantity(1);
        $orderLineDetails->setProductPrice($shippingAmount);
        $lineItem->setOrderLineDetails($orderLineDetails);

        $this->lineItems[] = $lineItem;
    }

    /**
     * @param float $price
     * @return int
     */
    private function preparePrice(float $price): int
    {
        return (int) round($price * 100);
    }

    /**
     * @param OrderItemInterface $item
     * @return float
     */
    private function getDiscountAmount(OrderItemInterface $item): float
    {
        if ($item->getProductType() === Type::TYPE_CODE) {
            $discountAmount = 0;

            foreach ($item->getChildrenItems() as $child) {
                $discountAmount += $child->getDiscountAmount();
            }

            return (float) $discountAmount;
        }

        return (float) $item->getDiscountAmount();
    }

    /**
     * @param OrderInterface $order
     * @param array $buildSubject
     * @return bool
     */
    private function skipLineItems(OrderInterface $order, array $buildSubject): bool
    {
        $shippingAmount = $this->preparePrice((float) $order->getShippingAmount());
        $cartGrandTotal = $this->preparePrice((float) $this->subjectReader->readAmount($buildSubject));

        return (bool) ($cartGrandTotal - $this->cartTotal - $shippingAmount);
    }
}
