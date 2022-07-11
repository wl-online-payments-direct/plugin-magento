<?php

declare(strict_types=1);

namespace Worldline\Payment\Plugin\Checkout\Controller\Cart;

use Exception;
use Magento\Checkout\Model\Session;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\Ui\HostedCheckout\ConfigProvider;
use Worldline\Payment\Model\WorldlineConfig;

class Index
{
    public const REJECTED = 'REJECTED';

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var ClientProvider
     */
    private $modelClient;

    /**
     * @param Session $checkoutSession
     * @param WorldlineConfig $worldlineConfig
     * @param ClientProvider $modelClient
     */
    public function __construct(
        Session $checkoutSession,
        WorldlineConfig $worldlineConfig,
        ClientProvider $modelClient
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->worldlineConfig = $worldlineConfig;
        $this->modelClient = $modelClient;
    }

    /**
     * @return void
     */
    public function beforeExecute()
    {
        if ($this->isQuoteRestore()) {
            $this->checkoutSession->restoreQuote();
        }
    }

    /**
     * @return bool
     */
    private function isQuoteRestore(): bool
    {
        try {
            $payment = $this->checkoutSession->getLastRealOrder()->getPayment();
            if (!$payment || ($payment->getMethod() !== ConfigProvider::HC_CODE)) {
                return false;
            }

            $hostedCheckoutResponse = $this->modelClient
                ->getClient()
                ->merchant($this->worldlineConfig->getMerchantId())
                ->hostedCheckout()
                ->getHostedCheckout($payment->getLastTransId());

            $worldlinePayment = $hostedCheckoutResponse->getCreatedPaymentOutput()->getPayment();
            if ($worldlinePayment === null || $worldlinePayment->getStatus() == self::REJECTED) {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }
}
