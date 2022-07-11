<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Http\Client\HostedCheckout;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\CreateHostedCheckoutResponse;
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
    private $clientProvider;

    /**
     * @var CreateHostedCheckoutRequestBuilder
     */
    private $createHostedCheckoutRequestBuilder;

    /**
     * @param LoggerInterface $logger
     * @param WorldlineConfig $worldlineConfig
     * @param ClientProvider $clientProvider
     * @param CreateHostedCheckoutRequestBuilder $createHostedCheckoutRequestBuilder
     */
    public function __construct(
        LoggerInterface $logger,
        WorldlineConfig $worldlineConfig,
        ClientProvider $clientProvider,
        CreateHostedCheckoutRequestBuilder $createHostedCheckoutRequestBuilder
    ) {
        parent::__construct($logger);
        $this->worldlineConfig = $worldlineConfig;
        $this->clientProvider = $clientProvider;
        $this->createHostedCheckoutRequestBuilder = $createHostedCheckoutRequestBuilder;
    }

    /**
     * @param array $data
     * @return CreateHostedCheckoutResponse
     * @throws LocalizedException
     */
    protected function process(array $data): CreateHostedCheckoutResponse
    {
        try {
            return $this->clientProvider->getClient()
                ->merchant($this->worldlineConfig->getMerchantId())
                ->hostedCheckout()
                ->createHostedCheckout($this->createHostedCheckoutRequestBuilder->build($data));
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            throw new LocalizedException(__('Sorry, but something went wrong'));
        }
    }
}
