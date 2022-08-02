<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Webhook;

use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderFactory;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\Payment\Api\WebhookProcessorInterface;
use Worldline\Payment\HostedCheckout\Vault\VaultRepository;
use Worldline\Payment\Model\Webhook\ProcessorInterface;

class PlaceOrderProcessor implements ProcessorInterface
{
    public const AUTHORIZE_CODE = 5;
    public const CAPTURE_CODE = 9;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var VaultRepository
     */
    private $vaultRepository;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    public function __construct(
        QuoteManagement $quoteManagement,
        VaultRepository $vaultRepository,
        OrderFactory $orderFactory
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->vaultRepository = $vaultRepository;
        $this->orderFactory = $orderFactory;
    }

    public function process(WebhooksEvent $webhookEvent, $quote)
    {
        $statusCode = (int)$webhookEvent->getPayment()->getStatusOutput()->getStatusCode();
        if (!in_array($statusCode, [self::AUTHORIZE_CODE, self::CAPTURE_CODE])) {
            return;
        }

        $order = $this->orderFactory->create()->loadByIncrementId($quote->getReservedOrderId());
        if ($order->getId()) {
            return;
        }

        $orderId = $this->quoteManagement->placeOrder($quote->getId());
        $hostedCheckoutId = (string)$quote->getPayment()->getAdditionalInformation('hosted_checkout_id');
        $this->vaultRepository->save((int)$orderId, $hostedCheckoutId);
    }
}
