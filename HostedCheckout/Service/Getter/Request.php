<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Service\Getter;

use OnlinePayments\Sdk\Domain\GetHostedCheckoutResponse;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\Config\WorldlineConfig;

class Request
{
    private $cachedRequests = [];

    /**
     * @var ClientProvider
     */
    private $clientProvider;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    public function __construct(
        ClientProvider $clientProvider,
        WorldlineConfig $worldlineConfig
    ) {
        $this->clientProvider = $clientProvider;
        $this->worldlineConfig = $worldlineConfig;
    }

    /**
     * Documentation:
     * @link: https://support.direct.ingenico.com/documentation/api/reference/#operation/GetHostedCheckoutApi
     *
     * @param string $hostedCheckoutId
     * @return GetHostedCheckoutResponse
     * @throws \Exception
     */
    public function create(string $hostedCheckoutId): GetHostedCheckoutResponse
    {
        if (!isset($this->cachedRequests[$hostedCheckoutId])) {
            $this->cachedRequests[$hostedCheckoutId] = $this->clientProvider->getClient()
                ->merchant($this->worldlineConfig->getMerchantId())
                ->hostedCheckout()
                ->getHostedCheckout($hostedCheckoutId);
        }

        return $this->cachedRequests[$hostedCheckoutId];
    }
}
