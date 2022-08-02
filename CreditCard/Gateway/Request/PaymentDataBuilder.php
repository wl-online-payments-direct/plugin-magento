<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use OnlinePayments\Sdk\Domain\AmountOfMoneyFactory;
use OnlinePayments\Sdk\Domain\OrderReferencesFactory;
use Worldline\Payment\Gateway\SubjectReader;
use Worldline\Payment\CreditCard\UI\ConfigProvider;

class PaymentDataBuilder implements BuilderInterface
{
    public const AMOUNT = 'amount';
    public const REFERENCES = 'references';
    public const TOKEN = 'token';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var AmountOfMoneyFactory
     */
    private $amountOfMoneyFactory;

    /**
     * @var OrderReferencesFactory
     */
    private $orderReferencesFactory;

    public function __construct(
        SubjectReader $subjectReader,
        AmountOfMoneyFactory $amountOfMoneyFactory,
        OrderReferencesFactory $orderReferencesFactory
    ) {
        $this->subjectReader = $subjectReader;
        $this->amountOfMoneyFactory = $amountOfMoneyFactory;
        $this->orderReferencesFactory = $orderReferencesFactory;
    }

    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();

        $amountOfMoney = $this->amountOfMoneyFactory->create();
        $amount = (int)round($this->subjectReader->readAmount($buildSubject) * 100);
        $amountOfMoney->setAmount($amount);
        $amountOfMoney->setCurrencyCode($order->getCurrencyCode());

        $references = $this->orderReferencesFactory->create();
        $references->setMerchantReference($order->getOrderIncrementId());

        $token = $payment->getAdditionalInformation('token_id');
        if ($payment->getMethod() != ConfigProvider::CODE) {
            $token = null;
            $payment->setAdditionalInformation('token_id', $token);
        }
        if (empty($token)) {
            if ($vaultPaymentToken = $payment->getExtensionAttributes()->getVaultPaymentToken()) {
                $token = $vaultPaymentToken->getGatewayToken();
            }
        }

        return [
            self::AMOUNT => $amountOfMoney,
            self::REFERENCES => $references,
            self::TOKEN => $token,
        ];
    }
}
