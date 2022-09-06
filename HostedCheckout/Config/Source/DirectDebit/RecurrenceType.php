<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Config\Source\DirectDebit;

use Magento\Framework\Data\OptionSourceInterface;

class RecurrenceType implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'UNIQUE',
                'label' => __('Unique'),
            ],
            [
                'value' => 'RECURRING',
                'label' => __('Recurring'),
            ]
        ];
    }
}
