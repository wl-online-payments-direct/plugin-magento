<?php

declare(strict_types=1);

namespace Worldline\Payment\GraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Worldline\Payment\Model\Order\Service\HostedCheckoutProcessor;

class Hosted implements ResolverInterface
{
    /**
     * @var HostedCheckoutProcessor
     */
    private $paymentProcessor;

    /**
     * @param HostedCheckoutProcessor $paymentProcessor
     */
    public function __construct(
        HostedCheckoutProcessor $paymentProcessor
    ) {
        $this->paymentProcessor = $paymentProcessor;
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

        $result = $this->paymentProcessor->process($paymentId, $mac);
        if ($result === false) {
            return [
                'result' => 'fail',
                'orderIncrementId' => ''
            ];
        }

        return [
            'result' => 'success',
            'orderIncrementId' => $result
        ];
    }
}
