<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Vault;

use Magento\Sales\Api\OrderRepositoryInterface;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificOutput;
use Worldline\Payment\HostedCheckout\Service\Getter\Request;

class VaultRepository
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Request
     */
    private $getRequest;

    /**
     * @var VaultManager
     */
    private $vaultManager;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Request $getRequest,
        VaultManager $vaultManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->getRequest = $getRequest;
        $this->vaultManager = $vaultManager;
    }

    public function save(int $orderId, string $hostedCheckoutId): void
    {
        $order = $this->orderRepository->get($orderId);
        $cardPaymentMethodSpecificOutput = $this->getCardPaymentMethodSpecificOutput($hostedCheckoutId);
        if (!$cardPaymentMethodSpecificOutput) {
            return;
        }

        $order->getPayment()->setAdditionalInformation(
            'card_number',
            mb_substr($cardPaymentMethodSpecificOutput->getCard()->getCardNumber(), -4)
        );
        $order->getPayment()->setAdditionalInformation(
            'payment_product_id',
            $cardPaymentMethodSpecificOutput->getPaymentProductId()
        );

        if ($this->vaultManager->saveVault($order->getPayment(), $cardPaymentMethodSpecificOutput)) {
            $this->orderRepository->save($order);
        }
    }

    private function getCardPaymentMethodSpecificOutput(string $hostedCheckoutId): ?CardPaymentMethodSpecificOutput
    {
        try {
            $payment = $this->getRequest->create($hostedCheckoutId)
                ->getCreatedPaymentOutput()
                ->getPayment();
            if (!$payment) {
                return null;
            }

            return $payment->getPaymentOutput()->getCardPaymentMethodSpecificOutput();
        } catch (\Exception $e) {
            return null;
        }
    }
}
