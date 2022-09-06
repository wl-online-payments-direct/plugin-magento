<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\DataAssigner;

use Magento\Quote\Api\Data\PaymentInterface;

/**
 * @core
 */
interface DataAssignerInterface
{
    public function assign(PaymentInterface $payment, array $additionalInformation): void;
}
