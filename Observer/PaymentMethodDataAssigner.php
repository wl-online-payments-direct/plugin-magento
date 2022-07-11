<?php

declare(strict_types=1);

namespace Worldline\Payment\Observer;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\WorldlineConfig;

class PaymentMethodDataAssigner extends AbstractDataAssignObserver
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
     * @var RequestInterface
     */
    private $request;

    /**
     * @param WorldlineConfig $worldlineConfig
     * @param ClientProvider $clientProvider
     * @param RequestInterface $request
     */
    public function __construct(
        WorldlineConfig $worldlineConfig,
        ClientProvider $clientProvider,
        RequestInterface $request
    ) {
        $this->worldlineConfig = $worldlineConfig;
        $this->clientProvider = $clientProvider;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(Observer $observer): void
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        $additionalInformation = $additionalData[OrderPaymentInterface::ADDITIONAL_INFORMATION] ?? $additionalData;
        if (!is_array($additionalInformation)
            || empty($additionalInformation)
        ) {
            return;
        }

        $payment = $this->readPaymentModelArgument($observer);
        if ($payment->getMethod() == \Worldline\Payment\Model\Ui\CreditCard\ConfigProvider::CODE &&
            isset($additionalInformation['hosted_tokenization_id']) &&
            (
                empty($payment->getAdditionalInformation('hosted_tokenization_id')) ||
                $payment->getAdditionalInformation('hosted_tokenization_id')
                != $additionalInformation['hosted_tokenization_id']
            )
        ) {
            $client = $this->clientProvider->getClient();
            $merchantId = $this->worldlineConfig->getMerchantId();
            $merchantClient = $client->merchant($merchantId);
            $createHostedTokenizationResponse = $merchantClient->hostedTokenization()->getHostedTokenization(
                $additionalInformation['hosted_tokenization_id']
            );
            $tokenResponse = $createHostedTokenizationResponse->getToken();
            $payment->setAdditionalInformation('token_id', $tokenResponse->getId() ?: '');
            $payment->setAdditionalInformation('payment_product_id', $tokenResponse->getPaymentProductId());
            $payment->setAdditionalInformation(
                'card_number',
                mb_substr($tokenResponse->getCard()->getAlias(), -4)
            );
            $payment->setAdditionalInformation(
                'hosted_tokenization_id',
                $additionalInformation['hosted_tokenization_id']
            );

            $this->addDeviceData($payment, $additionalInformation);

            if (isset($additionalData['is_active_payment_token_enabler'])) {
                $payment->setAdditionalInformation(
                    'is_active_payment_token_enabler',
                    $additionalInformation['is_active_payment_token_enabler']
                    && ($createHostedTokenizationResponse->getToken()->getIsTemporary() === false)
                );
            }
        }
    }

    /**
     * @param InfoInterface $payment
     * @param array $additionalInformation
     * @return void
     */
    private function addDeviceData(InfoInterface $payment, array $additionalInformation)
    {
        $payment->setAdditionalInformation(
            'device',
            [
                'AcceptHeader' => $this->request->getHeader('accept'),
                'UserAgent' => $this->request->getHeader('user-agent'),
                'Locale' => $additionalInformation['locale'] ?? '',
                'TimezoneOffsetUtcMinutes' => $additionalInformation['TimezoneOffsetUtcMinutes'] ?? '',
                'BrowserData' => [
                    'ColorDepth' => $additionalInformation['ColorDepth'] ?? '',
                    'JavaEnabled' => (bool) ($additionalInformation['JavaEnabled'] ?? false),
                    'ScreenHeight' => $additionalInformation['ScreenHeight'] ?? '',
                    'ScreenWidth' => $additionalInformation['ScreenWidth'] ?? '',
                ],
            ]
        );
    }
}
