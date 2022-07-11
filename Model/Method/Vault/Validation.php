<?php
declare(strict_types=1);

namespace Worldline\Payment\Model\Method\Vault;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;

class Validation
{
    /**
     * @var PaymentTokenManagementInterface
     */
    private $paymentTokenManagement;

    public function __construct(PaymentTokenManagementInterface $paymentTokenManagement)
    {
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * @param CartInterface $quote
     * @return bool
     */
    public function guestQuoteValidation(CartInterface $quote): bool
    {
        if ($quote->getCustomerIsGuest()) {
            return false;
        }

        return true;
    }

    /**
     * @param CartInterface $quote
     * @param string $paymentCode
     * @return bool
     */
    public function customerHasTokensValidation(CartInterface $quote, string $paymentCode): bool
    {
        $customerId = (int)$quote->getCustomer()->getId();
        $tokens = $this->paymentTokenManagement->getListByCustomerId($customerId);
        /** @var PaymentTokenInterface $token */
        foreach ($tokens as $token) {
            if ($token->getIsActive() && $token->getIsVisible() && $token->getPaymentMethodCode() === $paymentCode) {
                return true;
            }
        }

        return false;
    }
}
