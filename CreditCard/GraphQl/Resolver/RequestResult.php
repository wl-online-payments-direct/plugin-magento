<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\GraphQl\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Worldline\Payment\CreditCard\ReturnRequestProcessor;

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
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $hostedTokenizationId = $args['paymentId'] ?? null;
        if (!$hostedTokenizationId) {
            return [];
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        sleep(2); // wait for the webhook

        try {
            return [
                'result' => 'success',
                'orderIncrementId' => $this->returnRequestProcessor->processRequest($hostedTokenizationId)
            ];
        } catch (LocalizedException $e) {
            return [
                'result' => 'fail',
                'orderIncrementId' => ''
            ];
        }
    }
}
