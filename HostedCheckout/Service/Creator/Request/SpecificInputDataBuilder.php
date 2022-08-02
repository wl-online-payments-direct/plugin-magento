<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Service\Creator\Request;

use Magento\Framework\Locale\Resolver;
use Magento\Store\Model\StoreManagerInterface;
use OnlinePayments\Sdk\Domain\HostedCheckoutSpecificInput;
use OnlinePayments\Sdk\Domain\HostedCheckoutSpecificInputFactory;
use Worldline\Payment\HostedCheckout\Gateway\Config\Config;

class SpecificInputDataBuilder
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
     * @var HostedCheckoutSpecificInputFactory
     */
    private $hostedCheckoutSpecificInputFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Config $config,
        Resolver $store,
        HostedCheckoutSpecificInputFactory $hostedCheckoutSpecificInputFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->store = $store;
        $this->hostedCheckoutSpecificInputFactory = $hostedCheckoutSpecificInputFactory;
        $this->storeManager = $storeManager;
    }

    public function build(): HostedCheckoutSpecificInput
    {
        $hostedCheckoutSpecificInput = $this->hostedCheckoutSpecificInputFactory->create();
        $hostedCheckoutSpecificInput->setLocale($this->store->getLocale());
        $currentStoreId = (int) $this->storeManager->getStore()->getId();

        $hostedCheckoutSpecificInput->setReturnUrl($this->config->getReturnUrl($currentStoreId));
        if ($variant = $this->config->getTemplateId()) {
            $hostedCheckoutSpecificInput->setVariant($variant);
        }

        return $hostedCheckoutSpecificInput;
    }
}
