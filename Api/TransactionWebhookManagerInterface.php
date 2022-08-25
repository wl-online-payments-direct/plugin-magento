<?php

declare(strict_types=1);

namespace Worldline\Payment\Api;

use OnlinePayments\Sdk\Domain\WebhooksEvent;

interface TransactionWebhookManagerInterface
{
    public function saveTransaction(WebhooksEvent $webhookEvent): void;
}
