<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\GraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class VaultDataProvider
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
