<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Http\Client\HostedCheckout;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Store\Model\StoreManagerInterface;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInputFactory;
use OnlinePayments\Sdk\Domain\CreateHostedCheckoutRequest;
use OnlinePayments\Sdk\Domain\CreateHostedCheckoutRequestFactory;
use OnlinePayments\Sdk\Domain\HostedCheckoutSpecificInputFactory;
use OnlinePayments\Sdk\Domain\OrderFactory;
use OnlinePayments\Sdk\Domain\RedirectPaymentMethodSpecificInputFactory;
use Worldline\Payment\Gateway\Config\HostedCheckout\Config;

class CreateHostedCheckoutRequestBuilder
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Resolver
     */
    private $store;

    /**
     * @var CreateHostedCheckoutRequestFactory
     */
    private $createHostedCheckoutRequestFactory;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var HostedCheckoutSpecificInputFactory
     */
    private $hostedCheckoutSpecificInputFactory;

    /**
     * @var CardPaymentMethodSpecificInputFactory
     */
    private $cardPaymentMethodSpecificInputFactory;

    /**
     * @var RedirectPaymentMethodSpecificInputFactory
     */
    private $redirectPaymentMethodSpecificInputFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Config $config
     * @param Resolver $store
     * @param CreateHostedCheckoutRequestFactory $createHostedCheckoutRequestFactory
     * @param OrderFactory $orderFactory
     * @param HostedCheckoutSpecificInputFactory $hostedCheckoutSpecificInputFactory
     * @param CardPaymentMethodSpecificInputFactory $cardPaymentMethodSpecificInputFactory
     * @param RedirectPaymentMethodSpecificInputFactory $redirectPaymentMethodSpecificInputFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        Resolver $store,
        CreateHostedCheckoutRequestFactory $createHostedCheckoutRequestFactory,
        OrderFactory $orderFactory,
        HostedCheckoutSpecificInputFactory $hostedCheckoutSpecificInputFactory,
        CardPaymentMethodSpecificInputFactory $cardPaymentMethodSpecificInputFactory,
        RedirectPaymentMethodSpecificInputFactory $redirectPaymentMethodSpecificInputFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->store = $store;
        $this->createHostedCheckoutRequestFactory = $createHostedCheckoutRequestFactory;
        $this->orderFactory = $orderFactory;
        $this->hostedCheckoutSpecificInputFactory = $hostedCheckoutSpecificInputFactory;
        $this->cardPaymentMethodSpecificInputFactory = $cardPaymentMethodSpecificInputFactory;
        $this->redirectPaymentMethodSpecificInputFactory = $redirectPaymentMethodSpecificInputFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $data
     * @return CreateHostedCheckoutRequest
     * @throws NoSuchEntityException
     */
    public function build(array $data): CreateHostedCheckoutRequest
    {
        $createHostedCheckoutRequest = $this->createHostedCheckoutRequestFactory->create();
        $this->setOrder($createHostedCheckoutRequest, $data);
        $this->setHostedCheckoutSpecificInput($createHostedCheckoutRequest);
        $this->setCardPaymentMethodSpecificInput($createHostedCheckoutRequest, $data);

        return $createHostedCheckoutRequest;
    }

    /**
     * @param CreateHostedCheckoutRequest $createHostedCheckoutRequest
     * @param array $data
     * @return void
     */
    private function setOrder(
        CreateHostedCheckoutRequest $createHostedCheckoutRequest,
        array $data
    ) {
        $order = $this->orderFactory->create();
        $order->setAmountOfMoney($data['amount']);
        $order->setCustomer($data['customer']);
        $order->setReferences($data['references']);
        $order->setShipping($data['shipping']);
        if ($this->config->isCartLines()) {
            $order->setShoppingCart($data['cart']);
        }
        $createHostedCheckoutRequest->setOrder($order);
    }

    /**
     * @param CreateHostedCheckoutRequest $createHostedCheckoutRequest
     * @return void
     * @throws NoSuchEntityException
     */
    private function setHostedCheckoutSpecificInput(
        CreateHostedCheckoutRequest $createHostedCheckoutRequest
    ) {
        $hostedCheckoutSpecificInput = $this->hostedCheckoutSpecificInputFactory->create();
        $hostedCheckoutSpecificInput->setLocale($this->store->getLocale());
        $currentStoreId = (int) $this->storeManager->getStore()->getId();

        $hostedCheckoutSpecificInput->setReturnUrl($this->config->getReturnUrl($currentStoreId));
        if ($variant = $this->config->getTemplateId()) {
            $hostedCheckoutSpecificInput->setVariant($variant);
        }

        $createHostedCheckoutRequest->setHostedCheckoutSpecificInput($hostedCheckoutSpecificInput);
    }

    /**
     * @param CreateHostedCheckoutRequest $createHostedCheckoutRequest
     * @param array $data
     * @return void
     */
    private function setCardPaymentMethodSpecificInput(
        CreateHostedCheckoutRequest $createHostedCheckoutRequest,
        array $data
    ) {
        $cardPaymentMethodSpecificInput = $this->cardPaymentMethodSpecificInputFactory->create();
        $cardPaymentMethodSpecificInput->setThreeDSecure($data['threedsecure']);

        if (isset($data['token'])) {
            $cardPaymentMethodSpecificInput->setToken($data['token']);
        }

        $redirectPaymentMethodSpecificInput = $this->redirectPaymentMethodSpecificInputFactory->create();
        $redirectPaymentMethodSpecificInput->setRequiresApproval(true);
        $authMode = $this->config->getAuthorizationMode();
        if (isset($data['authorization_mode'])) {
            $authMode = $data['authorization_mode'];
            $redirectPaymentMethodSpecificInput->setRequiresApproval(false);
        }

        if ($authMode === Config::AUTHORIZATION_MODE_SALE) {
            $redirectPaymentMethodSpecificInput->setRequiresApproval(false);
        }

        $createHostedCheckoutRequest->setRedirectPaymentMethodSpecificInput($redirectPaymentMethodSpecificInput);
        $cardPaymentMethodSpecificInput->setAuthorizationMode($authMode);
        $createHostedCheckoutRequest->setCardPaymentMethodSpecificInput($cardPaymentMethodSpecificInput);
    }
}
