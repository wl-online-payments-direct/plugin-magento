<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway;

use InvalidArgumentException;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper;
use OnlinePayments\Sdk\DataObject;
use OnlinePayments\Sdk\Domain\CreatePaymentResponse;

class SubjectReader
{
    /**
     * Reads response object from subject
     *
     * @param array $subject
     * @return object
     */
    public function readResponseObject(array $subject): object
    {
        $response = Helper\SubjectReader::readResponse($subject);
        if (!isset($response['object']) || !is_object($response['object'])) {
            throw new InvalidArgumentException('Response object does not exist');
        }

        return $response['object'];
    }

    /**
     * Reads payment from subject
     *
     * @param array $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject): PaymentDataObjectInterface
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * Reads transaction from the subject.
     *
     * @param array $subject
     * @return DataObject
     * @throws InvalidArgumentException if the subject doesn't contain transaction details.
     */
    public function readTransaction(array $subject): DataObject
    {
        if (!isset($subject['object']) || !is_object($subject['object'])) {
            throw new InvalidArgumentException('Response object does not exist.');
        }

        if ($subject['object'] instanceof CreatePaymentResponse) {
            $transaction = $subject['object']->getPayment();
        } else {
            $transaction = $subject['object'];
        }

        if (empty($transaction)
            || !$transaction instanceof DataObject
        ) {
            throw new InvalidArgumentException('The object is not a class \OnlinePayments\Sdk\DataObject.');
        }

        return $transaction;
    }

    /**
     * Reads action from the subject.
     *
     * @param array $subject
     * @return DataObject | null
     * @throws InvalidArgumentException if the subject doesn't contain transaction details.
     */
    public function readMerchantAction(array $subject)
    {
        if (!isset($subject['object']) || !is_object($subject['object'])) {
            throw new InvalidArgumentException('Response object does not exist.');
        }

        if ($subject['object'] instanceof CreatePaymentResponse) {
            $transaction = $subject['object']->getMerchantAction();
        } else {
            $transaction = $subject['object'];
        }

        return $transaction;
    }

    /**
     * Reads amount from subject
     *
     * @param array $subject
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        if (!isset($subject['amount'])
            && isset($subject['payment'])
            && ($subject['payment'] instanceof PaymentDataObject)) {
            $subject['amount'] = $subject['payment']->getPayment()->getOrder()->getGrandTotal();
        }

        return Helper\SubjectReader::readAmount($subject);
    }
}
