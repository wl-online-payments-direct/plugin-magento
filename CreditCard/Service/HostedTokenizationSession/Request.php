<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Service\HostedTokenizationSession;

use Magento\Framework\App\RequestInterface;
use OnlinePayments\Sdk\Domain\GetHostedTokenizationResponse;
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

    public function __construct(
        WorldlineConfig $worldlineConfig,
        ClientProvider $clientProvider
    ) {
        $this->worldlineConfig = $worldlineConfig;
        $this->clientProvider = $clientProvider;
    }

    /**
     * @param string $hostedTokenizationId
     * @return GetHostedTokenizationResponse
     * @throws \Exception
     */
    public function execute(string $hostedTokenizationId): GetHostedTokenizationResponse
    {
        return $this->clientProvider->getClient()
            ->merchant($this->worldlineConfig->getMerchantId())
            ->hostedTokenization()
            ->getHostedTokenization($hostedTokenizationId);
    }
}
