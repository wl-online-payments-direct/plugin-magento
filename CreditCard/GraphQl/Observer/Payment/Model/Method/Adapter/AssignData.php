<?php
declare(strict_types=1);

namespace Worldline\Payment\CreditCard\GraphQl\Observer\Payment\Model\Method\Adapter;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class AssignData implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $enteredData = $observer->getData('data')->getData('additional_data');
        /** @var \Magento\Quote\Model\Quote\Payment $paymentModel */
        $paymentModel = $observer->getData("payment_model");
        $additionalInfo = array_merge((array)$paymentModel->getAdditionalInformation(), $enteredData);
        $paymentModel->setAdditionalInformation($additionalInfo);
    }
}
