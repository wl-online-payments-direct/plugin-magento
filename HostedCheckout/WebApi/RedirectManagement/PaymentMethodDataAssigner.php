<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\WebApi\RedirectManagement;

use Magento\Quote\Api\Data\PaymentInterface;
use Worldline\Payment\Model\DataAssigner\DataAssignerInterface;

class PaymentMethodDataAssigner implements DataAssignerInterface
{
    public function assign(PaymentInterface $payment, array $additionalInformation): void
    {
        if (isset($additionalInformation['is_active_payment_token_enabler'])) {
            $payment->setAdditionalInformation(
                'is_active_payment_token_enabler',
                $additionalInformation['is_active_payment_token_enabler']
            );
        }
    }
}
