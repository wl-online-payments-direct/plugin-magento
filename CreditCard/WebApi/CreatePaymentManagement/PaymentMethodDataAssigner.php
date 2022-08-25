<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\WebApi\CreatePaymentManagement;

use Magento\Quote\Api\Data\PaymentInterface;
use Worldline\Payment\Model\DataAssigner\DataAssignerInterface;
use Worldline\Payment\CreditCard\Service\HostedTokenizationSession\Request as RequestHostedTokenizationSession;

class PaymentMethodDataAssigner implements DataAssignerInterface
{
    /**
     * @var RequestHostedTokenizationSession
     */
    private $requestHostedTokenizationSession;

    public function __construct(
        RequestHostedTokenizationSession $requestHostedTokenizationSession
    ) {
        $this->requestHostedTokenizationSession = $requestHostedTokenizationSession;
    }

    public function assign(PaymentInterface $payment, array $additionalInformation): void
    {
        $hostedTokenizationId = $additionalInformation['hosted_tokenization_id'] ?? '';
        if (!$hostedTokenizationId) {
            $hostedTokenizationId = (string)$payment->getAdditionalInformation('hosted_tokenization_id');
        }

        $createHostedTokenizationResponse = $this->requestHostedTokenizationSession->execute($hostedTokenizationId);

        $tokenResponse = $createHostedTokenizationResponse->getToken();
        $payment->setAdditionalInformation('token_id', $tokenResponse->getId() ?: '');
        $payment->setAdditionalInformation('payment_product_id', $tokenResponse->getPaymentProductId());
        $payment->setAdditionalInformation(
            'card_number',
            mb_substr($tokenResponse->getCard()->getAlias(), -4)
        );
        $payment->setAdditionalInformation('hosted_tokenization_id', $hostedTokenizationId);

        if (isset($additionalInformation['is_active_payment_token_enabler'])) {
            $payment->setAdditionalInformation(
                'is_active_payment_token_enabler',
                $additionalInformation['is_active_payment_token_enabler']
                && ($tokenResponse->getIsTemporary() === false)
            );
        }
    }
}
