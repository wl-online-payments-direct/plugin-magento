<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\HostedCheckout;

use Exception;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificOutput;
use Worldline\Payment\Model\CardDate;
use Worldline\Payment\Model\WorldlineConfig;

class VaultManager
{
    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    private $paymentExtensionFactory;

    /**
     * @var PaymentTokenFactoryInterface
     */
    private $paymentTokenFactory;

    /**
     * @var CardDate
     */
    private $cardDate;

    /**
     * @param WorldlineConfig $worldlineConfig
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @param CardDate $cardDate
     */
    public function __construct(
        WorldlineConfig $worldlineConfig,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        PaymentTokenFactoryInterface $paymentTokenFactory,
        CardDate $cardDate
    ) {
        $this->worldlineConfig = $worldlineConfig;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->cardDate = $cardDate;
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param CardPaymentMethodSpecificOutput $cardPaymentMethodSpecificOutput
     * @return void
     * @throws Exception
     */
    public function setVault(
        OrderPaymentInterface $payment,
        CardPaymentMethodSpecificOutput $cardPaymentMethodSpecificOutput
    ) {
        $paymentToken = $this->getVaultPaymentToken($cardPaymentMethodSpecificOutput);
        if (null === $paymentToken) {
            return;
        }

        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
        }
        $extensionAttributes->setVaultPaymentToken($paymentToken);
        $payment->setAdditionalInformation(VaultConfigProvider::IS_ACTIVE_CODE, true);
        $payment->setExtensionAttributes($extensionAttributes);
    }

    /**
     * @param CardPaymentMethodSpecificOutput $cardPaymentMethodSpecificOutput
     * @return PaymentTokenInterface|null
     * @throws Exception
     */
    private function getVaultPaymentToken(
        CardPaymentMethodSpecificOutput $cardPaymentMethodSpecificOutput
    ): ?PaymentTokenInterface {
        $token = $cardPaymentMethodSpecificOutput->getToken();
        $card = $cardPaymentMethodSpecificOutput->getCard();
        if (!$token || !$card->getExpiryDate()) {
            return null;
        }

        $paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
        $paymentToken->setGatewayToken($token);
        $expirationDate = $this->cardDate->getExpirationDateAt($cardPaymentMethodSpecificOutput);
        $paymentToken->setExpiresAt($expirationDate);
        $paymentToken->setTokenDetails($this->cardDate->convertDetailsToJSON([
            'type' => $this->worldlineConfig->mapCcType($cardPaymentMethodSpecificOutput->getPaymentProductId()),
            'maskedCC' => $card->getCardNumber(),
            'expirationDate' => $this->cardDate->getExpirationDate($cardPaymentMethodSpecificOutput)
        ]));

        return $paymentToken;
    }
}
