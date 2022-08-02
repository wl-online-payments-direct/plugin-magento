<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\UI;

use Exception;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Worldline\Payment\CreditCard\Gateway\Config\Config;
use Worldline\Payment\CreditCard\UI\ConfigProvider\CreateHostedTokenizationResponseProcessor;
use Worldline\Payment\Model\Ui\PaymentIconsProvider;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string
     */
    public const CODE = 'worldline_cc';

    /**
     * @var string
     */
    public const CC_VAULT_CODE = 'worldline_cc_vault';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CreateHostedTokenizationResponseProcessor
     */
    private $createHostedTokenizationResponseProcessor;

    /**
     * @var PaymentIconsProvider
     */
    private $iconProvider;

    public function __construct(
        LoggerInterface $logger,
        Config $config,
        StoreManagerInterface $storeManager,
        CreateHostedTokenizationResponseProcessor $createHostedTokenizationResponseProcessor,
        PaymentIconsProvider $iconProvider
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->createHostedTokenizationResponseProcessor = $createHostedTokenizationResponseProcessor;
        $this->iconProvider = $iconProvider;
    }

    public function getConfig(): array
    {
        try {
            $storeId = (int) $this->storeManager->getStore()->getId();
            $createHostedTokenizationResponse = $this->createHostedTokenizationResponseProcessor->buildAndProcess();

            return [
                'payment' => [
                    self::CODE => [
                        'isActive' => $this->config->isActive($storeId),
                        'url' => "https://payment.{$createHostedTokenizationResponse->getPartialRedirectUrl()}",
                        'icons' => $this->iconProvider->getCcIcons($storeId),
                        'ccVaultCode' => self::CC_VAULT_CODE
                    ]
                ]
            ];
        } catch (Exception $e) {
            $this->logger->critical($e);
            return [];
        }
    }
}
