<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\GraphQl\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Worldline\Payment\HostedCheckout\ReturnRequestProcessor;

class RequestResult implements ResolverInterface
{
    /**
     * @var ReturnRequestProcessor
     */
    private $returnRequestProcessor;

    public function __construct(
        ReturnRequestProcessor $returnRequestProcessor
    ) {
        $this->returnRequestProcessor = $returnRequestProcessor;
    }

    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $paymentId = $args['paymentId'] ?? '';
        $mac = $args['mac'] ?? '';
        if (!$paymentId || !$mac) {
            return [];
        }

        try {
            return [
                'result' => 'success',
                'orderIncrementId' => $this->returnRequestProcessor->processRequest($paymentId, $mac)
            ];
        } catch (LocalizedException $e) {
            return [
                'result' => 'fail',
                'orderIncrementId' => ''
            ];
        }
    }
}
