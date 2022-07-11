<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Response\HostedCheckout;

use Magento\Payment\Gateway\Response\HandlerInterface;
use OnlinePayments\Sdk\Domain\CreateHostedCheckoutResponse;
use Worldline\Payment\Gateway\SubjectReader;

class SessionDetailsHandler implements HandlerInterface
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
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $hostedCheckoutResponse = $response['object'] ?? false;

        if (!$hostedCheckoutResponse instanceof CreateHostedCheckoutResponse) {
            throw new \InvalidArgumentException('Response object does not exist.');
        }

        $payment->setAdditionalInformation(
            'redirectURL',
            'https://payment.' . $hostedCheckoutResponse->getPartialRedirectUrl()
        );
        $payment->setAdditionalInformation('RETURNMAC', $hostedCheckoutResponse->getRETURNMAC());
        $payment->setWorldlinePaymentId($hostedCheckoutResponse->getHostedCheckoutId());
        $payment->setIsTransactionClosed(false);
        $payment->setShouldCloseParentTransaction(false);
        $payment->setCcStatusDescription('REDIRECTED');
        $payment->setIsTransactionPending(true);
        // prevent sending email straight away
        $payment->getOrder()->setCanSendNewEmailFlag(false);
    }
}
