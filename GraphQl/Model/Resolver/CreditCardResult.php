<?php
declare(strict_types=1);

namespace Worldline\Payment\GraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Worldline\Payment\Model\Order\Service\CreditCardProcessor;

class CreditCardResult implements ResolverInterface
{
    /**
     * @var CreditCardProcessor
     */
    private $paymentProcessor;

    public function __construct(CreditCardProcessor $paymentProcessor)
    {
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
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $mac = $args['mac'] ?? null;
        $paymentId = $args['paymentId'] ?? null;

        if (!$paymentId || !$mac) {
            return [];
        }

        if ($this->paymentProcessor->getPaymentReturnMac($paymentId) != $mac) {
            return [];
        }

        $paymentStatusCode = $this->paymentProcessor->process($paymentId);
        if (!in_array($paymentStatusCode, CreditCardProcessor::SUCCESS_STATUS_CODES)) {
            return [
                'result' => 'fail',
                'orderIncrementId' => ''
            ];
        }

        return [
            'result' => 'success',
            'orderIncrementId' => $this->paymentProcessor->getIncrementId($paymentId)
        ];
    }
}
