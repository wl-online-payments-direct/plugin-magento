<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Http\Client;

use Exception;
use OnlinePayments\Sdk\DataObject;
use OnlinePayments\Sdk\Domain\CancelPaymentResponse;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\WorldlineConfig;

class TransactionVoid extends AbstractTransaction
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
     * @param array $data
     * @return DataObject|CancelPaymentResponse
     * @throws Exception
     */
    protected function process(array $data)
    {
        $client = $this->modelClient->getClient();
        $merchantId = $this->worldlineConfig->getMerchantId();
        // @TODO implement exceptions catching

        $payment = $client->merchant($merchantId)->payments()->getPayment($data['transaction_id']);

        if ($payment->getStatusOutput()->getIsCancellable()) {
            return $client
                ->merchant($merchantId)
                ->payments()
                ->cancelPayment($data['transaction_id']);
        }

        return $payment;
    }
}
