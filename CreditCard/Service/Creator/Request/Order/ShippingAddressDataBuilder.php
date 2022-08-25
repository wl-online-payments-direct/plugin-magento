<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Service\Creator\Request\Order;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\AddressPersonalFactory;
use OnlinePayments\Sdk\Domain\PersonalNameFactory;
use OnlinePayments\Sdk\Domain\Shipping;
use OnlinePayments\Sdk\Domain\ShippingFactory;

class ShippingAddressDataBuilder
{
    /**
     * @var ShippingFactory
     */
    private $shippingFactory;

    /**
     * @var AddressPersonalFactory
     */
    private $addressPersonalFactory;

    /**
     * @var PersonalNameFactory
     */
    private $personalNameFactory;

    /**
     * @param ShippingFactory $shippingFactory
     * @param AddressPersonalFactory $addressPersonalFactory
     * @param PersonalNameFactory $personalNameFactory
     */
    public function __construct(
        ShippingFactory $shippingFactory,
        AddressPersonalFactory $addressPersonalFactory,
        PersonalNameFactory $personalNameFactory
    ) {
        $this->shippingFactory = $shippingFactory;
        $this->addressPersonalFactory = $addressPersonalFactory;
        $this->personalNameFactory = $personalNameFactory;
    }

    public function build(CartInterface $quote): Shipping
    {
        $shippingAddress = $quote->getShippingAddress();
        $shipping = $this->shippingFactory->create();

        if (!$shippingAddress) {
            return $shipping;
        }

        $name = $this->personalNameFactory->create();
        $name->setFirstName($shippingAddress->getFirstname());
        $name->setSurname($shippingAddress->getLastname());
        $name->setTitle($shippingAddress->getPrefix());

        $addressPersonal = $this->addressPersonalFactory->create();
        $addressPersonal->setName($name);
        $addressPersonal->setCity($shippingAddress->getCity());
        $addressPersonal->setCountryCode($shippingAddress->getCountryId());
        $addressPersonal->setState($shippingAddress->getRegionCode());
        $addressPersonal->setStreet(implode(', ', (array) $shippingAddress->getStreet()));
        $addressPersonal->setZip($shippingAddress->getPostcode());

        $shipping->setAddress($addressPersonal);

        return $shipping;
    }
}
