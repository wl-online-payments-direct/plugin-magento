<?php

declare(strict_types=1);

namespace Worldline\Payment\Logger\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigDebugProvider
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string[]|null
     */
    private $data;

    public function __construct(ScopeConfigInterface $scopeConfig, array $data = [])
    {
        $this->scopeConfig = $scopeConfig;
        $this->data = $data;
    }

    public function getLogMode(?int $storeId = null): int
    {
        $xmlConfigPath = $this->data['log_mode'] ?? '';
        if (!$xmlConfigPath) {
            return 0;
        }

        return (int)$this->scopeConfig->getValue($xmlConfigPath, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
