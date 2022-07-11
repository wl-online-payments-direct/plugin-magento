<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Http\Client;

use OnlinePayments\Sdk\Domain\CapturePaymentRequest;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\WorldlineConfig;

class TransactionSubmitForSettlement extends AbstractTransaction
{
    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var ClientProvider
     */
    private $modelClient;

    /**
     * @param LoggerInterface $logger
     * @param WorldlineConfig $worldlineConfig
     * @param ClientProvider $modelClient
     */
    public function __construct(
        LoggerInterface $logger,
        WorldlineConfig $worldlineConfig,
        ClientProvider $modelClient
    ) {
        parent::__construct($logger);
        $this->worldlineConfig = $worldlineConfig;
        $this->modelClient = $modelClient;
    }

    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        $capturePaymentRequest = new CapturePaymentRequest();
        $capturePaymentRequest->setAmount($data['amount']);

        $client = $this->modelClient->getClient();
        $merchantId = $this->worldlineConfig->getMerchantId();
        // @TODO implement exceptions catching
        $capturePaymentResponse = $client
            ->merchant($merchantId)
            ->payments()
            ->capturePayment($data['transaction_id'], $capturePaymentRequest);

        return $capturePaymentResponse;
    }
}
