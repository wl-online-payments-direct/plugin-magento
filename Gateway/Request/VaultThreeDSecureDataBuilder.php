<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class VaultThreeDSecureDataBuilder implements BuilderInterface
{
    /**
     * @var ThreeDSecureDataBuilder
     */
    private $threeDSecureDataBuilder;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param ThreeDSecureDataBuilder $threeDSecureDataBuilder
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ThreeDSecureDataBuilder $threeDSecureDataBuilder,
        SubjectReader $subjectReader
    ) {
        $this->threeDSecureDataBuilder = $threeDSecureDataBuilder;
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
        if ($payment->getAdditionalInformation('is_multishipping')) {
            return [];
        }

        return $this->threeDSecureDataBuilder->build($buildSubject);
    }
}
