<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Gateway\Request;

use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\InfoInterface;
use OnlinePayments\Sdk\Domain\AddressFactory;
use OnlinePayments\Sdk\Domain\CompanyInformationFactory;
use OnlinePayments\Sdk\Domain\ContactDetailsFactory;
use OnlinePayments\Sdk\Domain\Customer;
use OnlinePayments\Sdk\Domain\CustomerFactory;
use OnlinePayments\Sdk\Domain\PersonalInformationFactory;
use OnlinePayments\Sdk\Domain\PersonalNameFactory;
use Worldline\Payment\Gateway\Request\Customer\DeviceDataBuilder;
use Worldline\Payment\Gateway\SubjectReader;

class CustomerDataBuilder implements BuilderInterface
{
    /**
     * Customer object name
     */
    private const CUSTOMER = 'customer';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

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
        SubjectReader $subjectReader,
        CustomerFactory $customerFactory,
        PersonalNameFactory $personalNameFactory,
        PersonalInformationFactory $personalInformationFactory,
        CompanyInformationFactory $companyInformationFactory,
        AddressFactory $addressFactory,
        ContactDetailsFactory $contactDetailsFactory,
        DeviceDataBuilder $deviceDataBuilder
    ) {
        $this->subjectReader = $subjectReader;
        $this->customerFactory = $customerFactory;
        $this->personalNameFactory = $personalNameFactory;
        $this->personalInformationFactory = $personalInformationFactory;
        $this->companyInformationFactory = $companyInformationFactory;
        $this->addressFactory = $addressFactory;
        $this->contactDetailsFactory = $contactDetailsFactory;
        $this->deviceDataBuilder = $deviceDataBuilder;
    }

    public function build(array $buildSubject): array
    {
        $this->init($buildSubject);

        $this->addPersonalInformation();
        $this->addCompanyInformation();
        $this->addAddressInformation();
        $this->addContactDetails();
        $this->addDeviceData();

        return [
            self::CUSTOMER => $this->customer
        ];
    }

    private function init(array $buildSubject): void
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $this->billingAddress = $order->getBillingAddress();
        $this->payment = $paymentDO->getPayment();
        $this->customer = $this->customerFactory->create();
        $this->customer->setMerchantCustomerId($order->getCustomerId());
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
        $address->setCity($this->billingAddress->getCity());
        $address->setZip($this->billingAddress->getPostcode());
        $address->setState($this->billingAddress->getRegionCode());
        $address->setCountryCode($this->billingAddress->getCountryId());
        $address->setStreet($this->billingAddress->getStreetLine1() . ', ' . $this->billingAddress->getStreetLine2());
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
