<?php

declare(strict_types=1);

namespace Worldline\Payment\Plugin\Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Form;

use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Form;

class ReplaceSaveUrl
{
    private const WORLDLINE = 'worldline';

    public function afterGetSaveUrl(Form $subject, string $result)
    {
        $paymentMethodName = $subject->getOrder()->getPayment()->getMethod();
        if (substr($paymentMethodName, 0, strlen(self::WORLDLINE)) !== self::WORLDLINE) {
            return $result;
        }

        return $subject->getUrl('worldline/order_creditmemo/save', ['_current' => true]);
    }
}
