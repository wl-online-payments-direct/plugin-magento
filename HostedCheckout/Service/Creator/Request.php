<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Service\Creator;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\CreateHostedCheckoutRequest;
use OnlinePayments\Sdk\Domain\CreateHostedCheckoutResponse;
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
    private $clientProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        WorldlineConfig $worldlineConfig,
        ClientProvider $clientProvider,
        LoggerInterface $logger
    ) {
        $this->worldlineConfig = $worldlineConfig;
        $this->clientProvider = $clientProvider;
        $this->logger = $logger;
    }

    /**
     * Documentation:
     * @see https://support.direct.ingenico.com/documentation/api/reference/#operation/CreateHostedCheckoutApi
     *
     * @param CreateHostedCheckoutRequest $request
     * @return CreateHostedCheckoutResponse
     * @throws LocalizedException
     */
    public function create(CreateHostedCheckoutRequest $request): CreateHostedCheckoutResponse
    {
        try {
            return $this->clientProvider->getClient()
                ->merchant($this->worldlineConfig->getMerchantId())
                ->hostedCheckout()
                ->createHostedCheckout($request);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            throw new LocalizedException(__('Sorry, but something went wrong'));
        }
    }
}
