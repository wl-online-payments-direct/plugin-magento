<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Webhook\Handler;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\Payment\Model\AdditionalInfoInterface;
use Worldline\Payment\Model\Ui\PaymentProductsProvider;
use Magento\Sales\Model\OrderFactory;

class PaymentInformation implements HandlerInterface
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    public function __construct(
        OrderFactory $orderFactory
    ) {
        $this->orderFactory = $orderFactory;
    }

    public function handle(WebhooksEvent $webhookEvent, CartInterface $quote): void
    {
        $payment = $this->getPayment($quote);

        $amountModel = $webhookEvent->getPayment()->getPaymentOutput()->getAmountOfMoney();
        $cardPaymentMethod = $webhookEvent->getPayment()->getPaymentOutput()->getCardPaymentMethodSpecificOutput();
        $redirectPaymentMethod = $webhookEvent->getPayment()->getPaymentOutput()
            ->getRedirectPaymentMethodSpecificOutput();

        $payment->setAdditionalInformation(
            AdditionalInfoInterface::KEY_STATUS,
            $webhookEvent->getPayment()->getStatus()
        )->setAdditionalInformation(
            AdditionalInfoInterface::KEY_STATUS_CODE,
            $webhookEvent->getPayment()->getStatusOutput()->getStatusCode()
        )->setAdditionalInformation(
            AdditionalInfoInterface::KEY_PAYMENT_TRANSACTION_ID,
            $webhookEvent->getPayment()->getId()
        )->setAdditionalInformation(
            AdditionalInfoInterface::KEY_TOTAL,
            (((float)$amountModel->getAmount()) / 100) . ' ' . $amountModel->getCurrencyCode()
        );

        if ($cardPaymentMethod) {
            $payment->setAdditionalInformation(
                AdditionalInfoInterface::KEY_FRAUD_RESULT,
                ucfirst($cardPaymentMethod->getFraudResults()->getFraudServiceResult())
            )->setAdditionalInformation(
                AdditionalInfoInterface::KEY_PAYMENT_PRODUCT_ID,
                $cardPaymentMethod->getPaymentProductId()
            )->setAdditionalInformation(
                AdditionalInfoInterface::KEY_PAYMENT_METHOD,
                PaymentProductsProvider::PAYMENT_PRODUCTS[$cardPaymentMethod->getPaymentProductId()]['group']
            )->setAdditionalInformation(
                AdditionalInfoInterface::KEY_CARD_LAST_4,
                trim($cardPaymentMethod->getCard()->getCardNumber(), '*')
            );
        } elseif ($redirectPaymentMethod) {
            $payment->setAdditionalInformation(
                AdditionalInfoInterface::KEY_FRAUD_RESULT,
                ucfirst($redirectPaymentMethod->getFraudResults()->getFraudServiceResult())
            )->setAdditionalInformation(
                AdditionalInfoInterface::KEY_PAYMENT_PRODUCT_ID,
                $redirectPaymentMethod->getPaymentProductId()
            )->setAdditionalInformation(
                AdditionalInfoInterface::KEY_PAYMENT_METHOD,
                PaymentProductsProvider::PAYMENT_PRODUCTS[$redirectPaymentMethod->getPaymentProductId()]['group']
            );
        }

        $payment->save();
    }

    /**
     * @param CartInterface $quote
     * @return PaymentInterface|OrderPaymentInterface
     */
    private function getPayment(CartInterface $quote)
    {
        $quote->getReservedOrderId();
        $order = $this->orderFactory->create()->loadByIncrementId($quote->getReservedOrderId());
        return $order->getPayment() ?? $quote->getPayment();
    }
}
