<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\Payment\Api\WebhookProcessorInterface;

interface ProcessorInterface
{
    /**
     * @param WebhooksEvent $webhookEvent
     * @param CartInterface $quote
     *
     * @return void
     * @throws LocalizedException
     */
    public function process(WebhooksEvent $webhookEvent, CartInterface $quote);
}
