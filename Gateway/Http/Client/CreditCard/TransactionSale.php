<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Http\Client\CreditCard;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\CreatePaymentResponse;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Gateway\Http\Client\AbstractTransaction;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\WorldlineConfig;

class TransactionSale extends AbstractTransaction
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
     * @var CreatePaymentRequestBuilder
     */
    private $createPaymentRequestBuilder;

    /**
     * @param LoggerInterface $logger
     * @param WorldlineConfig $worldlineConfig
     * @param ClientProvider $modelClient
     * @param CreatePaymentRequestBuilder $createPaymentRequestBuilder
     */
    public function __construct(
        LoggerInterface $logger,
        WorldlineConfig $worldlineConfig,
        ClientProvider $modelClient,
        CreatePaymentRequestBuilder $createPaymentRequestBuilder
    ) {
        parent::__construct($logger);
        $this->worldlineConfig = $worldlineConfig;
        $this->modelClient = $modelClient;
        $this->createPaymentRequestBuilder = $createPaymentRequestBuilder;
    }

    /**
     * @param array $data
     * @return CreatePaymentResponse
     * @throws LocalizedException
     */
    protected function process(array $data): CreatePaymentResponse
    {
        try {
            return $this->modelClient->getClient()
                ->merchant($this->worldlineConfig->getMerchantId())
                ->payments()
                ->createPayment($this->createPaymentRequestBuilder->build($data));
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            throw new LocalizedException(__('Sorry, but something went wrong'));
        }
    }
}
