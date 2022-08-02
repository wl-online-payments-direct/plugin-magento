<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\GetHostedCheckoutResponse;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Gateway\Http\Client\AbstractTransaction;
use Worldline\Payment\HostedCheckout\Gateway\Request\PaymentDataBuilder;
use Worldline\Payment\HostedCheckout\Service\Getter\Request;

class TransactionSale extends AbstractTransaction
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(LoggerInterface $logger, Request $request)
    {
        parent::__construct($logger);
        $this->request = $request;
    }

    /**
     * @param array $data
     * @return GetHostedCheckoutResponse
     * @throws LocalizedException
     */
    protected function process(array $data): GetHostedCheckoutResponse
    {
        $hostedCheckoutId = (string) ($data[PaymentDataBuilder::HOSTED_CHECKOUT_ID] ?? '');
        if (!$hostedCheckoutId) {
            throw new LocalizedException(__('Hosted checkout id is missing'));
        }

        $response = $this->request->create($hostedCheckoutId);
        $this->writeLogIfNeeded($data, $response);

        return $response;
    }

    private function writeLogIfNeeded(array $data, GetHostedCheckoutResponse $response): void
    {
        $transactionAmountOfMoney = $response->getCreatedPaymentOutput()
            ->getPayment()
            ->getPaymentOutput()
            ->getAmountOfMoney()
            ->getAmount();
        $orderAmountOfMoney = $data[PaymentDataBuilder::AMOUNT] ?? 0;

        if ($transactionAmountOfMoney !== $orderAmountOfMoney) {
            $this->logger->warning(__('Wrong amount'), [
                'hosted_checkout_id' => $response->getCreatedPaymentOutput()->getPayment()->getId(),
                'transaction_amount_of_money' => $transactionAmountOfMoney,
                'order_amount_of_money' => $orderAmountOfMoney,
            ]);
        }
    }
}
