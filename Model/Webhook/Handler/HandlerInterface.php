<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Webhook\Handler;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\WebhooksEvent;

interface HandlerInterface
{
    /**
     * @param WebhooksEvent $webhookEvent
     * @param CartInterface $quote
     *
     * @return void
     * @throws LocalizedException
     */
    public function handle(WebhooksEvent $webhookEvent, CartInterface $quote): void;
}
