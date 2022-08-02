<?php
declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\GraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;

class AdditionalDataProvider implements AdditionalDataProviderInterface
{
    /**
     * @param array $data
     * @return array
     * @throws GraphQlInputException
     */
    public function getData(array $data): array
    {
        if (!isset($data['code'])) {
            throw new GraphQlInputException(
                __('Required parameter "code" for "payment_method" is missing.')
            );
        }

        return [$data['code']];
    }
}
