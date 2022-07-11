<?php

declare(strict_types=1);

namespace Worldline\Payment\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;

class VaultDataProvider implements AdditionalDataProviderInterface
{
    /**
     * Return Additional Data
     *
     * @param array $args
     * @return array
     * @throws GraphQlInputException
     */
    public function getData(array $args): array
    {
        if (!$args[$args['code']]['public_hash']) {
            throw new GraphQlInputException(__('No public_hash provided'));
        }
        return $args[$args['code']];
    }
}
