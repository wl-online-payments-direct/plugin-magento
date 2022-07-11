<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Http\Client\CreditCard;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInputFactory;
use OnlinePayments\Sdk\Domain\CreatePaymentRequest;
use OnlinePayments\Sdk\Domain\CreatePaymentRequestFactory;
use OnlinePayments\Sdk\Domain\OrderFactory;
use Worldline\Payment\Gateway\Config\Config;

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

    /**
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param CreatePaymentRequestFactory $createPaymentRequestFactory
     * @param OrderFactory $orderFactory
     * @param CardPaymentMethodSpecificInputFactory $cardPaymentMethodSpecificInputFactory
     */
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
     * @throws NoSuchEntityException
     */
    public function build(array $data): CreatePaymentRequest
    {
        $createPaymentRequest = $this->createPaymentRequestFactory->create();
        $this->addOrder($createPaymentRequest, $data);
        $this->addCardPaymentMethodSpecificInput($createPaymentRequest, $data);

        return $createPaymentRequest;
    }

    /**
     * @param CreatePaymentRequest $createPaymentRequest
     * @param array $data
     * @return void
     */
    private function addOrder(CreatePaymentRequest $createPaymentRequest, array $data)
    {
        $order = $this->orderFactory->create();
        $order->setAmountOfMoney($data['amount']);
        $order->setCustomer($data['customer']);
        $order->setReferences($data['references']);
        $order->setShipping($data['shipping']);
        $createPaymentRequest->setOrder($order);
    }

    /**
     * @param CreatePaymentRequest $createPaymentRequest
     * @param array $data
     * @return void
     * @throws NoSuchEntityException
     */
    private function addCardPaymentMethodSpecificInput(CreatePaymentRequest $createPaymentRequest, array $data)
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        $cardPaymentMethodSpecificInput = $this->cardPaymentMethodSpecificInputFactory->create();
        $cardPaymentMethodSpecificInput->setToken($data['token']);
        $cardPaymentMethodSpecificInput->setThreeDSecure($data['threedsecure']);
        $cardPaymentMethodSpecificInput->setReturnUrl($this->config->getReturnUrl($storeId));
        $cardPaymentMethodSpecificInput->setAuthorizationMode($this->getAuthModel($data));

        $createPaymentRequest->setCardPaymentMethodSpecificInput($cardPaymentMethodSpecificInput);
    }

    /**
     * @param array $data
     * @return string
     */
    private function getAuthModel(array $data): string
    {
        if (isset($data['authorization_mode'])) {
            return (string) $data['authorization_mode'];
        }

        return $this->config->getAuthorizationMode();
    }
}
