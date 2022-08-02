<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use OnlinePayments\Sdk\Domain\CapturePaymentRequestFactory;
use Worldline\Payment\Gateway\SubjectReader;

class CaptureDataBuilder implements BuilderInterface
{
    public const TRANSACTION_ID = 'transaction_id';
    public const CAPTURE_PAYMENT_REQUEST = 'capture_payment_request';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var CapturePaymentRequestFactory
     */
    private $capturePaymentRequestFactory;

    public function __construct(
        SubjectReader $subjectReader,
        CapturePaymentRequestFactory $capturePaymentRequestFactory
    ) {
        $this->subjectReader = $subjectReader;
        $this->capturePaymentRequestFactory = $capturePaymentRequestFactory;
    }

    /**
     * @param array $buildSubject
     * @return array
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $transactionId = $payment->getCcTransId();

        if (!$transactionId) {
            throw new LocalizedException(__('No authorization transaction to proceed capture.'));
        }

        $capturePaymentRequest = $this->capturePaymentRequestFactory->create();
        $capturePaymentRequest->setAmount((int) ($this->subjectReader->readAmount($buildSubject) * 100));

        return [
            self::TRANSACTION_ID => $transactionId,
            self::CAPTURE_PAYMENT_REQUEST => $capturePaymentRequest,
        ];
    }
}
