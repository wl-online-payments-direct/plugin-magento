<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Gateway\Http\Client;

use OnlinePayments\Sdk\Domain\CaptureResponse;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Gateway\Http\Client\AbstractTransaction;
use Worldline\Payment\HostedCheckout\Gateway\Request\CaptureDataBuilder;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\Config\WorldlineConfig;

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

    public function __construct(
        LoggerInterface $logger,
        WorldlineConfig $worldlineConfig,
        ClientProvider $modelClient
    ) {
        parent::__construct($logger);
        $this->worldlineConfig = $worldlineConfig;
        $this->modelClient = $modelClient;
    }

    protected function process(array $data): CaptureResponse
    {
        return $this->modelClient->getClient()
            ->merchant($this->worldlineConfig->getMerchantId())
            ->payments()
            ->capturePayment(
                $data[CaptureDataBuilder::TRANSACTION_ID],
                $data[CaptureDataBuilder::CAPTURE_PAYMENT_REQUEST]
            );
    }
}
