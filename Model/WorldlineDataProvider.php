<?php

declare(strict_types=1);

namespace Worldline\Payment\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;
use Worldline\Payment\Model\Ui\CreditCard\ConfigProvider;

class WorldlineDataProvider implements AdditionalDataProviderInterface
{
    /**
     * Format Worldline input into value expected when setting payment method.
     *
     * @param array $data
     * @return array
     * @throws GraphQlInputException
     */
    public function getData(array $data): array
    {
        if (!isset($data[ConfigProvider::CODE])) {
            throw new GraphQlInputException(
                __('Required parameter "worldline" for "payment_method" is missing.')
            );
        }

        return $data[ConfigProvider::CODE];
    }
}
