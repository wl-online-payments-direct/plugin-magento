<?php

namespace Worldline\Payment\Model;

use DateInterval;
use DateTime;
use DateTimeZone;
use Magento\Framework\Serialize\Serializer\Json;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificOutput;

class CardDate
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @param CardPaymentMethodSpecificOutput $cardPaymentMethodSpecificOutput
     * @return string
     * @throws \Exception
     */
    public function getExpirationDateAt(CardPaymentMethodSpecificOutput $cardPaymentMethodSpecificOutput): string
    {
        $card = $cardPaymentMethodSpecificOutput->getCard();
        $expirationDateAt = $this->processDate($card->getExpiryDate());
        $expirationDateAt->add(new DateInterval('P1M'));
        return $expirationDateAt->format('Y-m-d 00:00:00');
    }

    /**
     * @param CardPaymentMethodSpecificOutput $cardPaymentMethodSpecificOutput
     * @return string
     * @throws \Exception
     */
    public function getExpirationDate(CardPaymentMethodSpecificOutput $cardPaymentMethodSpecificOutput): string
    {
        $card = $cardPaymentMethodSpecificOutput->getCard();
        $expirationDate = $this->processDate($card->getExpiryDate());
        return $expirationDate->format('m/Y');
    }

    /**
     * @param string $date
     * @return DateTime
     * @throws \Exception
     */
    public function processDate(string $date): DateTime
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
     * @param array $details
     * @return string
     */
    public function convertDetailsToJSON(array $details): string
    {
        $json = $this->serializer->serialize($details);
        return $json ?: '{}';
    }
}
