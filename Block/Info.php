<?php

declare(strict_types=1);

namespace Worldline\Payment\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\ConfigurableInfo;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\CcConfigProvider;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Worldline\Payment\Model\WorldlineConfig;

class Info extends ConfigurableInfo
{
    public const WORLDLINE = 'worldline';

    /**
     * @var string
     */
    protected $_template = 'Worldline_Payment::info/default.phtml';

    /**
     * @var CcConfigProvider
     */
    private $iconsProvider;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var ?OrderPaymentInterface
     */
    private $payment;

    /**
     * @param Context $context
     * @param ConfigInterface $config
     * @param CcConfigProvider $iconsProvider
     * @param WorldlineConfig $worldlineConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        CcConfigProvider $iconsProvider,
        WorldlineConfig $worldlineConfig,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);
        $this->iconsProvider = $iconsProvider;
        $this->worldlineConfig = $worldlineConfig;
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function isWorldlinePayment(): bool
    {
        $payment = $this->getPayment();
        return substr($payment->getMethod(), 0, strlen(self::WORLDLINE)) === self::WORLDLINE;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getIconUrl(): string
    {
        return $this->getIconForType()['url'];
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getIconWidth(): int
    {
        return $this->getIconForType()['width'];
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getIconHeight(): int
    {
        return $this->getIconForType()['height'];
    }

    /**
     * @return Phrase
     * @throws LocalizedException
     */
    public function getIconTitle(): Phrase
    {
        return $this->getIconForType()['title'];
    }

    /**
     * @return string|null
     * @throws LocalizedException
     */
    public function getLast4Digits(): ?string
    {
        $payment = $this->getPayment();
        $last4Digits = $payment->getAdditionalInformation('card_number');
        return is_string($last4Digits) ? $last4Digits : null;
    }

    /**
     * @return OrderPaymentInterface
     * @throws LocalizedException
     */
    private function getPayment(): OrderPaymentInterface
    {
        if (null === $this->payment) {
            $this->payment = $this->getInfo()->getOrder()->getPayment();
        }

        return $this->payment;
    }

    /**
     * @return string|null
     * @throws LocalizedException
     */
    private function getPaymentCardType(): ?string
    {
        $payment = $this->getPayment();
        $type = $payment->getAdditionalInformation('payment_product_id');
        return $this->worldlineConfig->mapCcType((int) $type);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function getIconForType(): array
    {
        if (isset($this->iconsProvider->getIcons()[$this->getPaymentCardType()])) {
            return $this->iconsProvider->getIcons()[$this->getPaymentCardType()];
        }

        return [
            'url' => '',
            'width' => 0,
            'height' => 0
        ];
    }
}
