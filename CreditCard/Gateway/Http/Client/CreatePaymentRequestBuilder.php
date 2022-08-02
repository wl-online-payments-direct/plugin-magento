<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInputFactory;
use OnlinePayments\Sdk\Domain\CreatePaymentRequest;
use OnlinePayments\Sdk\Domain\CreatePaymentRequestFactory;
use OnlinePayments\Sdk\Domain\OrderFactory;
use Worldline\Payment\CreditCard\Gateway\Config\Config;
use Worldline\Payment\CreditCard\Gateway\Request\AddressDataBuilder;

class CreatePaymentRequestBuilder
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CreatePaymentRequestFactory
     */
    private $createPaymentRequestFactory;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var CardPaymentMethodSpecificInputFactory
     */
    private $cardPaymentMethodSpecificInputFactory;

    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        CreatePaymentRequestFactory $createPaymentRequestFactory,
        OrderFactory $orderFactory,
        CardPaymentMethodSpecificInputFactory $cardPaymentMethodSpecificInputFactory
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->createPaymentRequestFactory = $createPaymentRequestFactory;
        $this->orderFactory = $orderFactory;
        $this->cardPaymentMethodSpecificInputFactory = $cardPaymentMethodSpecificInputFactory;
    }

    /**
     * @param array $data
     * @return CreatePaymentRequest
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function build(array $data): CreatePaymentRequest
    {
        $createPaymentRequest = $this->createPaymentRequestFactory->create();
        $this->addOrder($createPaymentRequest, $data);
        $this->addCardPaymentMethodSpecificInput($createPaymentRequest, $data);

        return $createPaymentRequest;
    }

    private function addOrder(CreatePaymentRequest $createPaymentRequest, array $data): void
    {
        $order = $this->orderFactory->create();
        $order->setAmountOfMoney($data['amount']);
        $order->setCustomer($data['customer']);
        $order->setReferences($data['references']);
        $order->setShipping($data[AddressDataBuilder::SHIPPING_ADDRESS]);
        $createPaymentRequest->setOrder($order);
    }

    /**
     * @param CreatePaymentRequest $createPaymentRequest
     * @param array $data
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function addCardPaymentMethodSpecificInput(CreatePaymentRequest $createPaymentRequest, array $data): void
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        $cardPaymentMethodSpecificInput = $this->cardPaymentMethodSpecificInputFactory->create();
        $cardPaymentMethodSpecificInput->setToken($data['token']);
        $cardPaymentMethodSpecificInput->setThreeDSecure($data['threedsecure']);
        $cardPaymentMethodSpecificInput->setReturnUrl($this->config->getReturnUrl($storeId));
        $cardPaymentMethodSpecificInput->setAuthorizationMode($this->getAuthModel($data));

        $createPaymentRequest->setCardPaymentMethodSpecificInput($cardPaymentMethodSpecificInput);
    }

    private function getAuthModel(array $data): string
    {
        if (isset($data['authorization_mode'])) {
            return (string) $data['authorization_mode'];
        }

        return $this->config->getAuthorizationMode();
    }
}
