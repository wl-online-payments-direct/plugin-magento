<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\Payment\Api\WebhookProcessorInterface;

class GeneralProcessor implements ProcessorInterface
{
    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    public function __construct(
        array $processors = []
    ) {
        $this->processors = $processors;
    }

    /**
     * @param WebhooksEvent $webhookEvent
     * @return void
     * @throws LocalizedException
     */
    public function process(WebhooksEvent $webhookEvent)
    {
        $processor = $this->processors[$webhookEvent->getType()] ?? false;
        if (!$processor) {
            return;
        }

        $processor->process($webhookEvent);
    }
}
