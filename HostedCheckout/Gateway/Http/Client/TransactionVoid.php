<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Gateway\Http\Client;

use Exception;
use OnlinePayments\Sdk\DataObject;
use OnlinePayments\Sdk\Domain\CancelPaymentResponse;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Gateway\Http\Client\AbstractTransaction;
use Worldline\Payment\HostedCheckout\Gateway\Request\VoidDataBuilder;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\Config\WorldlineConfig;

class TransactionVoid extends AbstractTransaction
{
    /**
     * @var \Worldline\Payment\Model\Config\WorldlineConfig
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
        $payment = $this->modelClient->getClient()
            ->merchant($this->worldlineConfig->getMerchantId())
            ->payments()
            ->getPayment($data[VoidDataBuilder::TRANSACTION_ID]);

        if ($payment->getStatusOutput()->getIsCancellable()) {
            return $this->modelClient->getClient()
                ->merchant($this->worldlineConfig->getMerchantId())
                ->payments()
                ->cancelPayment($data[VoidDataBuilder::TRANSACTION_ID]);
        }

        return $payment;
    }
}
