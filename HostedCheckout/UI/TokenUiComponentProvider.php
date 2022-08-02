<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\UI;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;

class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    /**
     * @var TokenUiComponentInterfaceFactory
     */
    private $componentFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param TokenUiComponentInterfaceFactory $componentFactory
     * @param Json $json
     */
    public function __construct(
        TokenUiComponentInterfaceFactory $componentFactory,
        Json $json
    ) {
        $this->componentFactory = $componentFactory;
        $this->json = $json;
    }

    /**
     * Get UI component for token
     * @param PaymentTokenInterface $paymentToken
     * @return TokenUiComponentInterface
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken): TokenUiComponentInterface
    {
        $jsonDetails = $this->json->unserialize($paymentToken->getTokenDetails() ?: '{}');
        return $this->componentFactory->create(
            [
                'config' => [
                    'code' => ConfigProvider::HC_VAULT_CODE,
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
                    'public_hash' => $paymentToken->getPublicHash()
                ],
                'name' => 'Worldline_Payment/js/view/hosted-checkout/vault'
            ]
        );
    }
}
