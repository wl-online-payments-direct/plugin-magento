<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Ui\HostedCheckout;

use Exception;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Gateway\Config\HostedCheckout\Config;
use Worldline\Payment\Model\Ui\PaymentIconsProvider;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string
     */
    public const HC_CODE = 'worldline_hosted_checkout';

    /**
     * @var string
     */
    public const HC_VAULT_CODE = 'worldline_hosted_checkout_vault';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var PaymentIconsProvider
     */
    private $iconProvider;

    /**
     * @param LoggerInterface $logger
     * @param Config $config
     * @param SessionManagerInterface $session
     * @param PaymentIconsProvider $iconProvider
     */
    public function __construct(
        LoggerInterface $logger,
        Config $config,
        SessionManagerInterface $session,
        PaymentIconsProvider $iconProvider
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->session = $session;
        $this->iconProvider = $iconProvider;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        try {
            $storeId = (int) $this->session->getStoreId();

            return [
                'payment' => [
                    self::HC_CODE => [
                        'isActive' => $this->config->isActive($storeId),
                        'icons' => $this->iconProvider->getFilteredIcons([], $storeId),
                        'hcVaultCode' => self::HC_VAULT_CODE
                    ]
                ]
            ];
        } catch (Exception $e) {
            $this->logger->critical($e);
            return [];
        }
    }
}
