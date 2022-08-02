<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\Payment\Api\WebhookProcessorInterface;
use Worldline\Payment\HostedCheckout\ResourceModel\Quote as QuoteResource;
use Worldline\Payment\Model\Webhook\Handler\HandlerInterface;

class GeneralProcessor
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var HandlerInterface[]
     */
    private $handlers;

    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    public function __construct(
        QuoteResource $quoteResource,
        array $handlers = [],
        array $processors = []
    ) {
        $this->quoteResource = $quoteResource;
        $this->handlers = $handlers;
        $this->processors = $processors;
    }

    /**
     * @param WebhooksEvent $webhookEvent
     * @return void
     * @throws LocalizedException
     */
    public function process(WebhooksEvent $webhookEvent)
    {
        $orderIncrementId = (string)$webhookEvent->getPayment()
            ->getPaymentOutput()
            ->getReferences()
            ->getMerchantReference();

        $quote = $this->quoteResource->getQuoteByReservedOrderId($orderIncrementId);
        if (!$quote->getId()) {
            return;
        }

        foreach ($this->handlers as $handler) {
            $handler->handle($webhookEvent, $quote);
        }

        $paymentMethod = str_replace('_vault', '', $quote->getPayment()->getMethod());
        $key = $paymentMethod . '.' . $webhookEvent->getType();
        $processor = $this->processors[$key] ?? false;
        if (!$processor) {
            return;
        }

        $processor->process($webhookEvent, $quote);
    }
}
