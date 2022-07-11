<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CreditCardTypeOptions implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'americanexpress',
                'label' => __('American Express')
            ],
            [
                'value' => 'cartebancaire',
                'label' => __('Carte Bancaire')
            ],
            [
                'value' => 'dinersclub',
                'label' => __('Diners Club')
            ],
            [
                'value' => 'jcb',
                'label' => __('JCB')
            ],
            [
                'value' => 'maestro',
                'label' => __('Maestro')
            ],
            [
                'value' => 'mastercard',
                'label' => __('Mastercard')
            ],
            [
                'value' => 'visa',
                'label' => __('Visa')
            ]
        ];
    }
}
