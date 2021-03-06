<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Worldline\Payment\Gateway\SubjectReader;

class ThreeDSecureDetailsHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        $action = $this->subjectReader->readMerchantAction($response);
        if (empty($action)) {
            return;
        }

        $payment->setAdditionalInformation('actionType', $action->getActionType());
        if ($action->getActionType()  == 'REDIRECT') {
            $payment->setIsTransactionPending(true);
            $payment->setWorldlinePaymentId($payment->getTransactionId());
            $payment->setAdditionalInformation('redirectURL', $action->getRedirectData()->getRedirectURL());
            $payment->setAdditionalInformation('RETURNMAC', $action->getRedirectData()->getRETURNMAC());
        }
    }
}
