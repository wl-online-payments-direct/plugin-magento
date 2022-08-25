<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Service\Creator\Request\Order;

use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\AddressFactory;
use OnlinePayments\Sdk\Domain\CompanyInformationFactory;
use OnlinePayments\Sdk\Domain\ContactDetailsFactory;
use OnlinePayments\Sdk\Domain\Customer;
use OnlinePayments\Sdk\Domain\CustomerFactory;
use OnlinePayments\Sdk\Domain\PersonalInformationFactory;
use OnlinePayments\Sdk\Domain\PersonalNameFactory;
use Worldline\Payment\Gateway\Request\Customer\DeviceDataBuilder;

class CustomerDataBuilder
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var AddressAdapterInterface
     */
    private $billingAddress;

    /**
     * @var PersonalNameFactory
     */
    private $personalNameFactory;

    /**
     * @var PersonalInformationFactory
     */
    private $personalInformationFactory;

    /**
     * @var CompanyInformationFactory
     */
    private $companyInformationFactory;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var ContactDetailsFactory
     */
    private $contactDetailsFactory;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var InfoInterface
     */
    private $payment;

    /**
     * @var DeviceDataBuilder
     */
    private $deviceDataBuilder;

    public function __construct(
        CustomerFactory $customerFactory,
        PersonalNameFactory $personalNameFactory,
        PersonalInformationFactory $personalInformationFactory,
        CompanyInformationFactory $companyInformationFactory,
        AddressFactory $addressFactory,
        ContactDetailsFactory $contactDetailsFactory,
        DeviceDataBuilder $deviceDataBuilder
    ) {
        $this->customerFactory = $customerFactory;
        $this->personalNameFactory = $personalNameFactory;
        $this->personalInformationFactory = $personalInformationFactory;
        $this->companyInformationFactory = $companyInformationFactory;
        $this->addressFactory = $addressFactory;
        $this->contactDetailsFactory = $contactDetailsFactory;
        $this->deviceDataBuilder = $deviceDataBuilder;
    }

    public function build(CartInterface $quote): Customer
    {
        $this->init($quote);

        $this->addPersonalInformation();
        $this->addCompanyInformation();
        $this->addAddressInformation();
        $this->addContactDetails();
        $this->addDeviceData();

        return $this->customer;
    }

    private function init(CartInterface $quote): void
    {
        $this->billingAddress = $quote->getBillingAddress();
        $this->payment = $quote->getPayment();
        $this->customer = $this->customerFactory->create();
        $this->customer->setMerchantCustomerId($quote->getCustomerId());
    }

    private function addPersonalInformation(): void
    {
        $personalName = $this->personalNameFactory->create();
        $personalName->setTitle($this->billingAddress->getPrefix());
        $personalName->setFirstName($this->billingAddress->getFirstname());
        $personalName->setSurname($this->billingAddress->getLastname());
        $personalInformation = $this->personalInformationFactory->create();
        $personalInformation->setName($personalName);
        $this->customer->setPersonalInformation($personalInformation);
    }

    private function addCompanyInformation(): void
    {
        $companyInformation = $this->companyInformationFactory->create();
        $companyInformation->setName($this->billingAddress->getCompany());
        $this->customer->setCompanyInformation($companyInformation);
    }

    private function addAddressInformation(): void
    {
        $address = $this->addressFactory->create();
        $address->setStreet(implode(', ', (array) $this->billingAddress->getStreet()));
        $address->setZip($this->billingAddress->getPostcode());
        $address->setCity($this->billingAddress->getCity());
        $address->setState($this->billingAddress->getRegionCode());
        $address->setCountryCode($this->billingAddress->getCountryId());
        $this->customer->setBillingAddress($address);
    }

    private function addContactDetails(): void
    {
        $contactDetails = $this->contactDetailsFactory->create();
        $contactDetails->setEmailAddress($this->billingAddress->getEmail());
        $contactDetails->setPhoneNumber($this->billingAddress->getTelephone());
        $this->customer->setContactDetails($contactDetails);
    }

    private function addDeviceData(): void
    {
        $deviceData = $this->payment->getAdditionalInformation('device') ?? [];
        $customerDevice = $this->deviceDataBuilder->build($deviceData);
        $this->customer->setDevice($customerDevice);
    }
}
