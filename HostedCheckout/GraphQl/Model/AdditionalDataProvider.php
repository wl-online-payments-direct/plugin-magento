<?php
declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\GraphQl\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;

class AdditionalDataProvider implements AdditionalDataProviderInterface
{
    public const XML_PATH_PAYMENT_WORLDLINE_HC_VAULT_ACTIVE = "payment/worldline_hosted_checkout_vault/active";

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param array $data
     * @return array
     * @throws GraphQlInputException
     */
    public function getData(array $data): array
    {
        if (!isset($data['code'])) {
            throw new GraphQlInputException(
                __('Required parameter "code" for "payment_method" is missing.')
            );
        }

        return [
            'code' => $data['code'],
            'is_active_payment_token_enabler' => $this->isHCVaultEnable()
        ];
    }

    /**
     * @return bool
     */
    private function isHCVaultEnable(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_PAYMENT_WORLDLINE_HC_VAULT_ACTIVE);
    }
}
