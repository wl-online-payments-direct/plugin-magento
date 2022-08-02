<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use OnlinePayments\Sdk\Domain\AddressPersonalFactory;
use OnlinePayments\Sdk\Domain\PersonalNameFactory;
use OnlinePayments\Sdk\Domain\Shipping;
use OnlinePayments\Sdk\Domain\ShippingFactory;
use Worldline\Payment\Gateway\SubjectReader;

class AddressDataBuilder implements BuilderInterface
{
    /**
     * ShippingAddress block name
     */
    public const SHIPPING_ADDRESS = 'shipping';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

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

    public function __construct(
        SubjectReader $subjectReader,
        ShippingFactory $shippingFactory,
        AddressPersonalFactory $addressPersonalFactory,
        PersonalNameFactory $personalNameFactory
    ) {
        $this->subjectReader = $subjectReader;
        $this->shippingFactory = $shippingFactory;
        $this->addressPersonalFactory = $addressPersonalFactory;
        $this->personalNameFactory = $personalNameFactory;
    }

    /**
     * @param array $buildSubject
     * @return Shipping[]
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $order = $paymentDO->getOrder();

        $shippingAddress = $order->getShippingAddress();
        $shipping = $this->shippingFactory->create();

        if (!$shippingAddress) {
            return [
                self::SHIPPING_ADDRESS => $shipping
            ];
        }

        $name = $this->personalNameFactory->create();
        $name->setFirstName($shippingAddress->getFirstname());
        $name->setSurname($shippingAddress->getLastname());
        $name->setTitle($shippingAddress->getPrefix());

        $addressPersonal = $this->addressPersonalFactory->create();
        $addressPersonal->setName($name);
        $addressPersonal->setCity($shippingAddress->getCity());
        $addressPersonal->setZip($shippingAddress->getPostcode());
        $addressPersonal->setState($shippingAddress->getRegionCode());
        $addressPersonal->setCountryCode($shippingAddress->getCountryId());
        $addressPersonal->setStreet($shippingAddress->getStreetLine1() . ', ' . $shippingAddress->getStreetLine2());

        $shipping->setAddress($addressPersonal);

        return [
            self::SHIPPING_ADDRESS => $shipping
        ];
    }
}
