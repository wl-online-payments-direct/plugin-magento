<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Service\Creator\Request;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\Order;
use OnlinePayments\Sdk\Domain\OrderFactory;
use Worldline\Payment\HostedCheckout\Gateway\Config\Config;
use Worldline\Payment\HostedCheckout\Service\Creator\Request\Order\AmountDataBuilder;
use Worldline\Payment\HostedCheckout\Service\Creator\Request\Order\CustomerDataBuilder;
use Worldline\Payment\HostedCheckout\Service\Creator\Request\Order\ReferenceDataBuilder;
use Worldline\Payment\HostedCheckout\Service\Creator\Request\Order\ShippingAddressDataBuilder;
use Worldline\Payment\HostedCheckout\Service\Creator\Request\Order\ShoppingCartDataBuilder;

class OrderDataBuilder
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var AmountDataBuilder
     */
    private $amountDataBuilder;

    /**
     * @var CustomerDataBuilder
     */
    private $customerDataBuilder;

    /**
     * @var ReferenceDataBuilder
     */
    private $referenceDataBuilder;

    /**
     * @var ShippingAddressDataBuilder
     */
    private $shippingAddressDataBuilder;

    /**
     * @var ShoppingCartDataBuilder
     */
    private $shoppingCartDataBuilder;

    public function __construct(
        Config $config,
        OrderFactory $orderFactory,
        AmountDataBuilder $amountDataBuilder,
        CustomerDataBuilder $customerDataBuilder,
        ReferenceDataBuilder $referenceDataBuilder,
        ShippingAddressDataBuilder $shippingAddressDataBuilder,
        ShoppingCartDataBuilder $shoppingCartDataBuilder
    ) {
        $this->config = $config;
        $this->orderFactory = $orderFactory;
        $this->amountDataBuilder = $amountDataBuilder;
        $this->customerDataBuilder = $customerDataBuilder;
        $this->referenceDataBuilder = $referenceDataBuilder;
        $this->shippingAddressDataBuilder = $shippingAddressDataBuilder;
        $this->shoppingCartDataBuilder = $shoppingCartDataBuilder;
    }

    public function build(CartInterface $quote): Order
    {
        $order = $this->orderFactory->create();

        $order->setAmountOfMoney($this->amountDataBuilder->build($quote));
        $order->setCustomer($this->customerDataBuilder->build($quote));
        $order->setReferences($this->referenceDataBuilder->build($quote));
        $order->setShipping($this->shippingAddressDataBuilder->build($quote));

        if (!$this->config->isCartLines()) {
            return $order;
        }

        if ($cart = $this->shoppingCartDataBuilder->build($quote)) {
            $order->setShoppingCart($cart);
        }

        return $order;
    }
}
