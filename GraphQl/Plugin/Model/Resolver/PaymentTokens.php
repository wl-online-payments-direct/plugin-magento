<?php

declare(strict_types=1);

namespace Worldline\Payment\GraphQl\Plugin\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Vault\Model\PaymentTokenManagement;
use Worldline\Payment\CreditCard\UI\ConfigProvider;

class PaymentTokens
{
    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * @param PaymentTokenManagement $paymentTokenManagement
     */
    public function __construct(
        PaymentTokenManagement $paymentTokenManagement
    ) {
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * Modify results of resolve() call to add icons param
     *
     * @param ResolverInterface $subject
     * @param $resolvedValue
     * @param Field $field
     * @param $context
     * @return array
     *
     * @throws GraphQlAuthorizationException
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function afterResolve(
        ResolverInterface $subject,
        $resolvedValue,
        Field $field,
        $context
    ): array {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        foreach ($resolvedValue['items'] as $tokenId => $tokenData) {
            if ($tokenData['payment_method_code'] == ConfigProvider::CODE) {
                $token = $this->paymentTokenManagement
                    ->getByPublicHash($tokenData['public_hash'], $context->getUserId());
                if ($token) {
                    $resolvedValue['items'][$tokenId]['token'] = $token->getGatewayToken();
                }
            }
        }

        return $resolvedValue;
    }
}
