<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Worldline\Payment\Gateway\SubjectReader;

class PaymentDataBuilder implements BuilderInterface
{
    public const AMOUNT = 'amount';
    public const HOSTED_CHECKOUT_ID = 'hosted_checkout_id';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $amount = (int)round($this->subjectReader->readAmount($buildSubject) * 100);

        return [
            self::AMOUNT => $amount,
            self::HOSTED_CHECKOUT_ID => $payment->getAdditionalInformation('hosted_checkout_id'),
        ];
    }
}
