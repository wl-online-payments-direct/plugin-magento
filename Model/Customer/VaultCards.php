<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Customer;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenManagement;
use OnlinePayments\Sdk\Domain\CreateHostedTokenizationRequest;
use Worldline\Payment\Model\Ui\CreditCard\ConfigProvider;

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
     * @param CreateHostedTokenizationRequest $createHostedTokenizationRequest
     * @return void
     */
    public function setCurrentCustomerTokens(CreateHostedTokenizationRequest $createHostedTokenizationRequest): void
    {
        if (!$this->userContext->getUserId()) {
            return;
        }

        $tokens = $this->paymentTokenManagement->getListByCustomerId($this->userContext->getUserId());
        /** @var PaymentTokenInterface $token */
        foreach ($tokens as $token) {
            if ($token->getPaymentMethodCode() === ConfigProvider::CODE) {
                $customerTokens[] = $token->getGatewayToken();
            }
        }

        if (!isset($customerTokens)) {
            return;
        }

        if (count($customerTokens) > 1) {
            $createHostedTokenizationRequest->setTokens(implode(',', $customerTokens));
        } else {
            $createHostedTokenizationRequest->setTokens(current($customerTokens));
        }
    }
}
