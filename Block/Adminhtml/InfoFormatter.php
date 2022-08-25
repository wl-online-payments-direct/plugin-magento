<?php

declare(strict_types=1);

namespace Worldline\Payment\Block\Adminhtml;

use Worldline\Payment\Api\Data\PaymentInfoInterface;
use Worldline\Payment\Api\InfoFormatterInterface;

class InfoFormatter implements InfoFormatterInterface
{
    public function format(PaymentInfoInterface $paymentInfo): array
    {
        $data = [
            ['label' => __('Payment method'), 'value' => $paymentInfo->getPaymentMethod()],
            ['label' => __('Status'), 'value' => $paymentInfo->getStatus()],
            ['label' => __('Status code'), 'value' => $paymentInfo->getStatusCode()],
            ['label' => __('Fraud result'), 'value' => $paymentInfo->getFraudResult()],
            ['label' => __('Transaction number'), 'value' => $paymentInfo->getLastTransactionNumber()],
            [
                'label' => __('Total'),
                'value' => $paymentInfo->getAuthorizedAmount() . ' ' . $paymentInfo->getCurrency()
            ],
        ];

        if ($paymentInfo->getAmountAvailableForCapture()) {
            $data[] = [
                'label' => __('Amount available for capture'),
                'value' => $paymentInfo->getAmountAvailableForCapture() . ' ' . $paymentInfo->getCurrency()
            ];
        }

        if ($paymentInfo->getRefundedAmount()) {
            $data[] = [
                'label' => __('Refunded amount'),
                'value' => $paymentInfo->getRefundedAmount() . ' ' . $paymentInfo->getCurrency()
            ];
        }

        if ($paymentInfo->getAmountAvailableForRefund()) {
            $data[] = [
                'label' => __('Amount available for refund'),
                'value' => $paymentInfo->getAmountAvailableForRefund() . ' ' . $paymentInfo->getCurrency()
            ];
        }

        return $data;
    }
}
