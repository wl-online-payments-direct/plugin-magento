<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AllSpecificCustomerGroups implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 0, 'label' => __('All Allowed Customer Groups')],
            ['value' => 1, 'label' => __('Specific Customer Groups')]
        ];
    }
}
