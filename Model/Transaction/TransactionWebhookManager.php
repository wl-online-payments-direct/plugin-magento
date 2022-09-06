<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Transaction;

use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\DataObject;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\RefundResponse;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\Payment\Api\Data\TransactionInterface;
use Worldline\Payment\Api\Data\TransactionInterfaceFactory;
use Worldline\Payment\Api\TransactionRepositoryInterface;
use Worldline\Payment\Api\TransactionWebhookManagerInterface;
use Worldline\Payment\Model\Ui\PaymentProductsProvider;

class TransactionWebhookManager implements TransactionWebhookManagerInterface
{
    /**
     * @var TransactionInterfaceFactory
     */
    private $transactionFactory;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(
        TransactionInterfaceFactory $transactionFactory,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->transactionRepository = $transactionRepository;
    }

    public function saveTransaction(WebhooksEvent $webhookEvent): void
    {
        $worldlineResponse = $this->getResponse($webhookEvent);
        $statusCode = (int)$worldlineResponse->getStatusOutput()->getStatusCode();
        $output = $this->getOutput($worldlineResponse);

        $incrementId = (string)$output->getReferences()->getMerchantReference();
        $transaction = $this->transactionRepository->getLastTransaction($incrementId);

        if ($transaction
            && $transaction->getStatusCode() == $statusCode
            && $transaction->getTransactionId() == (string)$worldlineResponse->getId()
        ) {
            return;
        }

        $transaction = $this->transactionFactory->create();
        $this->addGeneralPaymentData($worldlineResponse, $transaction);

        if ($worldlineResponse instanceof PaymentResponse) {
            $this->addCardPaymentMethodData($worldlineResponse, $transaction);
            $this->addRedirectPaymentMethodData($worldlineResponse, $transaction);
        }

        $this->transactionRepository->save($transaction);
    }

    /**
     * @param WebhooksEvent $webhookEvent
     * @return DataObject
     * @throws LocalizedException
     */
    private function getResponse(WebhooksEvent $webhookEvent): DataObject
    {
        $response = null;
        if ($webhookEvent->getPayment()) {
            $response = $webhookEvent->getPayment();
        }

        if ($webhookEvent->getRefund()) {
            $response = $webhookEvent->getRefund();
        }

        if (!$response) {
            throw new LocalizedException(__('Invalid response model'));
        }

        return $response;
    }

    /**
     * @param DataObject $response
     * @return DataObject
     * @throws LocalizedException
     */
    private function getOutput(DataObject $response): DataObject
    {
        $output = null;
        if ($response instanceof PaymentResponse) {
            $output = $response->getPaymentOutput();
        }

        if ($response instanceof RefundResponse) {
            $output = $response->getRefundOutput();
        }

        if (!$output) {
            throw new LocalizedException(__('Invalid output model'));
        }

        return $output;
    }

    private function addGeneralPaymentData(DataObject $worldlineResponse, TransactionInterface $transaction): void
    {
        $output = $this->getOutput($worldlineResponse);
        $transaction->setIncrementId((string)$output->getReferences()->getMerchantReference());
        $transaction->setStatus((string)$worldlineResponse->getStatus());
        $transaction->setStatusCode((int)$worldlineResponse->getStatusOutput()->getStatusCode());
        $transaction->setTransactionId((string)$worldlineResponse->getId());
        $amount = (float) ($output->getAmountOfMoney()->getAmount() / 100);
        $transaction->setAmount($amount);
        $transaction->setCurrency($output->getAmountOfMoney()->getCurrencyCode());
    }

    private function addCardPaymentMethodData(DataObject $worldlineResponse, TransactionInterface $transaction): void
    {
        $cardPaymentMethod = $worldlineResponse->getPaymentOutput()->getCardPaymentMethodSpecificOutput();
        if (!$cardPaymentMethod) {
            return;
        }

        $transaction->setAdditionalData([
            TransactionInterface::FRAUD_RESULT =>
                ucfirst($cardPaymentMethod->getFraudResults()->getFraudServiceResult()),
            TransactionInterface::PAYMENT_PRODUCT_ID => $cardPaymentMethod->getPaymentProductId(),
            TransactionInterface::PAYMENT_METHOD =>
                PaymentProductsProvider::PAYMENT_PRODUCTS[$cardPaymentMethod->getPaymentProductId()]['group'],
            TransactionInterface::CARD_LAST_4 =>
                trim($cardPaymentMethod->getCard()->getCardNumber(), '*'),
        ]);
    }

    private function addRedirectPaymentMethodData(
        DataObject $worldlineResponse,
        TransactionInterface $transaction
    ): void {
        $redirectPaymentMethod = $worldlineResponse->getPaymentOutput()->getRedirectPaymentMethodSpecificOutput();
        if (!$redirectPaymentMethod) {
            return;
        }

        $transaction->setAdditionalData([
            TransactionInterface::FRAUD_RESULT =>
                ucfirst($redirectPaymentMethod->getFraudResults()->getFraudServiceResult()),
            TransactionInterface::PAYMENT_PRODUCT_ID => $redirectPaymentMethod->getPaymentProductId(),
            TransactionInterface::PAYMENT_METHOD =>
                PaymentProductsProvider::PAYMENT_PRODUCTS[$redirectPaymentMethod->getPaymentProductId()]['group'],
        ]);
    }
}
