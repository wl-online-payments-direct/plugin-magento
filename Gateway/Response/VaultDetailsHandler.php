<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Response;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use RuntimeException;
use Worldline\Payment\Gateway\SubjectReader;
use Worldline\Payment\Model\WorldlineConfig;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultDetailsHandler implements HandlerInterface
{
    /**
     * @var PaymentTokenFactoryInterface
     */
    private $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    private $paymentExtensionFactory;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param SubjectReader $subjectReader
     * @param WorldlineConfig $worldlineConfig
     * @param Json $serializer
     * @throws RuntimeException
     */
    public function __construct(
        PaymentTokenFactoryInterface $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        SubjectReader $subjectReader,
        WorldlineConfig $worldlineConfig,
        Json $serializer
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->subjectReader = $subjectReader;
        $this->worldlineConfig = $worldlineConfig;
        $this->serializer = $serializer;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $transaction = $this->subjectReader->readTransaction($response);
        $payment = $paymentDO->getPayment();
        if ($payment->getAdditionalInformation('is_active_payment_token_enabler')) {
            $paymentToken = $this->getVaultPaymentToken($transaction);
            if (null !== $paymentToken) {
                $extensionAttributes = $this->getExtensionAttributes($payment);
                $extensionAttributes->setVaultPaymentToken($paymentToken);
            }
        }
    }

    /**
     * Get vault payment token entity
     *
     * @param PaymentResponse $transaction
     * @return PaymentTokenInterface|null
     * @throws InputException|NoSuchEntityException|Exception
     */
    private function getVaultPaymentToken(PaymentResponse $transaction): ?PaymentTokenInterface
    {
        $cardPaymentMethodSpecificOutput = $transaction->getPaymentOutput()->getCardPaymentMethodSpecificOutput();
        $token = $cardPaymentMethodSpecificOutput->getToken();
        $card = $cardPaymentMethodSpecificOutput->getCard();
        if (empty($token) || empty($card->getExpiryDate())) {
            return null;
        }

        $paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
        $paymentToken->setGatewayToken($token);
        $expirationDate = $this->getExpirationDateAt($transaction);
        $paymentToken->setExpiresAt($expirationDate);
        $paymentToken->setTokenDetails($this->convertDetailsToJSON([
            'type' => $this->worldlineConfig->mapCcType($cardPaymentMethodSpecificOutput->getPaymentProductId()),
            'maskedCC' => $card->getCardNumber(),
            'expirationDate' => $this->getExpirationDate($transaction)
        ]));

        return $paymentToken;
    }

    /**
     * @param string $date
     * @return DateTime
     * @throws Exception
     */
    private function processDate(string $date): DateTime
    {
        return new DateTime(
            mb_substr($date, -2)
            . '-'
            . mb_substr($date, 0, 2)
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new DateTimeZone('UTC')
        );
    }

    /**
     * @param PaymentResponse $transaction
     * @return string
     * @throws Exception
     */
    private function getExpirationDate(PaymentResponse $transaction): string
    {
        $card = $transaction->getPaymentOutput()->getCardPaymentMethodSpecificOutput()->getCard();
        $expirationDate = $this->processDate($card->getExpiryDate());
        return $expirationDate->format('m/Y');
    }

    /**
     * @param PaymentResponse $transaction
     * @return string
     * @throws Exception
     */
    private function getExpirationDateAt(PaymentResponse $transaction): string
    {
        $card = $transaction->getPaymentOutput()->getCardPaymentMethodSpecificOutput()->getCard();
        $expirationDateAt = $this->processDate($card->getExpiryDate());
        $expirationDateAt->add(new DateInterval('P1M'));
        return $expirationDateAt->format('Y-m-d 00:00:00');
    }

    /**
     * Convert payment token details to JSON
     * @param array $details
     * @return string
     */
    private function convertDetailsToJSON(array $details): string
    {
        $json = $this->serializer->serialize($details);
        return $json ?: '{}';
    }

    /**
     * Get payment extension attributes
     *
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment): OrderPaymentExtensionInterface
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }

        return $extensionAttributes;
    }
}
