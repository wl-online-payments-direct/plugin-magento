<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Service\Creator;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\CreatePaymentRequest;
use OnlinePayments\Sdk\Domain\CreatePaymentResponse;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\Config\WorldlineConfig;

class Request
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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        WorldlineConfig $worldlineConfig,
        ClientProvider $modelClient
    ) {
        $this->worldlineConfig = $worldlineConfig;
        $this->modelClient = $modelClient;
        $this->logger = $logger;
    }

    /**
     * Documentation:
     * @link https://support.direct.ingenico.com/en/documentation/api/reference/#operation/CreatePaymentApi
     *
     * @param CreatePaymentRequest $request
     * @return CreatePaymentResponse
     * @throws LocalizedException
     */
    public function create(CreatePaymentRequest $request): CreatePaymentResponse
    {
        try {
            return $this->modelClient->getClient()
                ->merchant($this->worldlineConfig->getMerchantId())
                ->payments()
                ->createPayment($request);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            throw new LocalizedException(__('Sorry, but something went wrong'));
        }
    }
}
