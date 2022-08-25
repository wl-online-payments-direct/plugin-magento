<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Service\Getter;

use OnlinePayments\Sdk\Domain\PaymentResponse;
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
     * @link: https://support.direct.ingenico.com/en/documentation/api/reference/#operation/GetPaymentApi
     *
     * @param string $paymentId
     * @return PaymentResponse
     * @throws \Exception
     */
    public function create(string $paymentId): PaymentResponse
    {
        if (!isset($this->cachedRequests[$paymentId])) {
            $this->cachedRequests[$paymentId] = $this->clientProvider->getClient()
                ->merchant($this->worldlineConfig->getMerchantId())
                ->payments()
                ->getPayment($paymentId);
        }

        return $this->cachedRequests[$paymentId];
    }
}
