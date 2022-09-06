<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Config\Source\DirectDebit;

use Magento\Framework\Data\OptionSourceInterface;

class SignatureType implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'SMS',
                'label' => __('SMS'),
            ],
            [
                'value' => 'UNSIGNED',
                'label' => __('UNSIGNED'),
            ]
        ];
    }
}
