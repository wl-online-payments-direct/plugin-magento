<?php

declare(strict_types=1);

namespace Worldline\Payment\Api;

use Worldline\Payment\Api\Data\PaymentInfoInterface;

interface InfoFormatterInterface
{
    public function format(PaymentInfoInterface $paymentInfo): array;
}
