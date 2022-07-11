<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EnvironmentModeOptions implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 0,
                'label' => __('Test')
            ],
            [
                'value' => 1,
                'label' => __('Production')
            ]
        ];
    }
}
