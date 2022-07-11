<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Customer\HostedCheckout;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenManagement;
use OnlinePayments\Sdk\Domain\HostedCheckoutSpecificInput;
use Worldline\Payment\Model\Ui\HostedCheckout\ConfigProvider;

class VaultCards
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * @param UserContextInterface $userContext
     * @param PaymentTokenManagement $paymentTokenManagement
     */
    public function __construct(
        UserContextInterface $userContext,
        PaymentTokenManagement $paymentTokenManagement
    ) {
        $this->userContext = $userContext;
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * @param HostedCheckoutSpecificInput $hostedCheckoutSpecificInput
     * @return void
     */
    public function setCurrentCustomerTokens(HostedCheckoutSpecificInput $hostedCheckoutSpecificInput): void
    {
        if (!$this->userContext->getUserId()) {
            return;
        }

        $tokens = $this->paymentTokenManagement->getListByCustomerId(
            $this->userContext->getUserId()
        );
        if (count($tokens) == 0) {
            return;
        }

        $customerTokens = [];
        /** @var PaymentTokenInterface $token */
        foreach ($tokens as $token) {
            if ($token->getPaymentMethodCode() === ConfigProvider::HC_CODE) {
                $customerTokens[] = $token->getGatewayToken();
            }
        }
        $hostedCheckoutSpecificInput->setTokens(implode(',', $customerTokens));
    }
}
