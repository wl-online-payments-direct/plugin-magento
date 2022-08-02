<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Gateway\Http\Client;

use OnlinePayments\Sdk\DataObject;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Gateway\Http\Client\AbstractTransaction;
use Worldline\Payment\HostedCheckout\Gateway\Request\RefundDataBuilder;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\Config\WorldlineConfig;

class TransactionRefund extends AbstractTransaction
{
    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var ClientProvider
     */
    private $clientProvider;

    /**
     * @param LoggerInterface $logger
     * @param WorldlineConfig $worldlineConfig
     * @param ClientProvider $clientProvider
     */
    public function __construct(
        LoggerInterface $logger,
        WorldlineConfig $worldlineConfig,
        ClientProvider $clientProvider
    ) {
        parent::__construct($logger);
        $this->worldlineConfig = $worldlineConfig;
        $this->clientProvider = $clientProvider;
    }

    protected function process(array $data): DataObject
    {
        return $this->clientProvider->getClient()
            ->merchant($this->worldlineConfig->getMerchantId())
            ->payments()
            ->refundPayment(
                $data[RefundDataBuilder::TRANSACTION_ID],
                $data[RefundDataBuilder::REFUND_REQUEST]
            );
    }
}
