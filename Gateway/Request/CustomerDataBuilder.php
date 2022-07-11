<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Request;

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

    /**
     * @param SubjectReader $subjectReader
     * @param CustomerFactory $customerFactory
     * @param PersonalNameFactory $personalNameFactory
     * @param PersonalInformationFactory $personalInformationFactory
     * @param CompanyInformationFactory $companyInformationFactory
     * @param AddressFactory $addressFactory
     * @param ContactDetailsFactory $contactDetailsFactory
     * @param DeviceDataBuilder $deviceDataBuilder
     */
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

    /**
     * @param array $buildSubject
     * @return Customer[]
     */
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

    /**
     * @param array $buildSubject
     * @return void
     */
    private function init(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $this->billingAddress = $order->getBillingAddress();
        $this->payment = $paymentDO->getPayment();
        $this->customer = $this->customerFactory->create();
        $this->customer->setMerchantCustomerId($order->getCustomerId());
    }

    /**
     * @return void
     */
    private function addPersonalInformation()
    {
        $personalName = $this->personalNameFactory->create();
        $personalName->setTitle($this->billingAddress->getPrefix());
        $personalName->setFirstName($this->billingAddress->getFirstname());
        $personalName->setSurname($this->billingAddress->getLastname());
        $personalInformation = $this->personalInformationFactory->create();
        $personalInformation->setName($personalName);
        $this->customer->setPersonalInformation($personalInformation);
    }

    /**
     * @return void
     */
    private function addCompanyInformation()
    {
        $companyInformation = $this->companyInformationFactory->create();
        $companyInformation->setName($this->billingAddress->getCompany());
        $this->customer->setCompanyInformation($companyInformation);
    }

    /**
     * @return void
     */
    private function addAddressInformation()
    {
        $address = $this->addressFactory->create();
        $address->setStreet(implode(', ', (array) $this->billingAddress->getStreet()));
        $address->setZip($this->billingAddress->getPostcode());
        $address->setCity($this->billingAddress->getCity());
        $address->setState($this->billingAddress->getRegionCode());
        $address->setCountryCode($this->billingAddress->getCountryId());
        $this->customer->setBillingAddress($address);
    }

    /**
     * @return void
     */
    private function addContactDetails()
    {
        $contactDetails = $this->contactDetailsFactory->create();
        $contactDetails->setEmailAddress($this->billingAddress->getEmail());
        $contactDetails->setPhoneNumber($this->billingAddress->getTelephone());
        $this->customer->setContactDetails($contactDetails);
    }

    /**
     * @return void
     */
    private function addDeviceData()
    {
        $deviceData = $this->payment->getAdditionalInformation('device') ?? [];
        $customerDevice = $this->deviceDataBuilder->build($deviceData);
        $this->customer->setDevice($customerDevice);
    }
}
