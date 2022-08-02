<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use OnlinePayments\Sdk\Domain\GetHostedCheckoutResponse;
use Worldline\Payment\Gateway\SubjectReader;

class AmountResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        /** @var GetHostedCheckoutResponse $response */
        $response = $this->subjectReader->readResponseObject($validationSubject);
        $transactionAmountOfMoney = $response->getCreatedPaymentOutput()
            ->getPayment()
            ->getPaymentOutput()
            ->getAmountOfMoney()
            ->getAmount();

        $orderAmountOfMoney = (int)round($validationSubject['amount'] * 100);

        return $this->createResult($transactionAmountOfMoney === $orderAmountOfMoney);
    }
}
