<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\HostedCheckout;

use Exception;
use OnlinePayments\Sdk\Domain\GetHostedCheckoutResponse;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\WorldlineConfig;

class ResponseProvider
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
     * @param WorldlineConfig $worldlineConfig
     * @param ClientProvider $clientProvider
     */
    public function __construct(
        WorldlineConfig $worldlineConfig,
        ClientProvider $clientProvider
    ) {
        $this->worldlineConfig = $worldlineConfig;
        $this->clientProvider = $clientProvider;
    }

    /**
     * @param string $hostedCheckoutId
     * @return GetHostedCheckoutResponse|null
     */
    public function getHostedCheckoutResponse(string $hostedCheckoutId): ?GetHostedCheckoutResponse
    {
        try {
            return $this->clientProvider
                ->getClient()
                ->merchant($this->worldlineConfig->getMerchantId())
                ->hostedCheckout()
                ->getHostedCheckout($hostedCheckoutId);
        } catch (Exception $e) {
            return null;
        }
    }
}
