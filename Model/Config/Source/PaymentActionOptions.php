<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PaymentActionOptions implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'authorize',
                'label' => __('Authorize')
            ],
            [
                'value' => 'authorize_capture',
                'label' => __('Authorize & Capture')
            ]
        ];
    }
}
