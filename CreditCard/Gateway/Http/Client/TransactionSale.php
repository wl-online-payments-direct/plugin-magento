<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use Psr\Log\LoggerInterface;
use Worldline\Payment\CreditCard\Gateway\Request\PaymentDataBuilder;
use Worldline\Payment\CreditCard\Service\Getter\Request as GetterRequest;
use Worldline\Payment\Gateway\Http\Client\AbstractTransaction;

class TransactionSale extends AbstractTransaction
{
    /**
     * @var GetterRequest
     */
    private $getterRequest;

    public function __construct(
        LoggerInterface $logger,
        GetterRequest $getterRequest
    ) {
        parent::__construct($logger);
        $this->getterRequest = $getterRequest;
    }

    /**
     * @param array $data
     * @return PaymentResponse
     * @throws LocalizedException
     */
    protected function process(array $data): PaymentResponse
    {
        $paymentId = $data[PaymentDataBuilder::PAYMENT_ID] ?? false;
        if (!$paymentId) {
            throw new LocalizedException(__('Payment id is missing'));
        }

        $response = $this->getterRequest->create($paymentId);
        $this->writeLogIfNeeded($data, $response);

        return $response;
    }

    private function writeLogIfNeeded(array $data, PaymentResponse $response): void
    {
        $transactionAmountOfMoney = $response->getPaymentOutput()
            ->getAmountOfMoney()
            ->getAmount();
        $orderAmountOfMoney = $data[PaymentDataBuilder::AMOUNT] ?? 0;

        if ($transactionAmountOfMoney !== $orderAmountOfMoney) {
            $this->logger->warning(__('Wrong amount'), [
                'credit_card_payment_id' => $response->getId(),
                'transaction_amount_of_money' => $transactionAmountOfMoney,
                'order_amount_of_money' => $orderAmountOfMoney,
            ]);
            throw new LocalizedException(__('Wrong amount'));
        }
    }
}
