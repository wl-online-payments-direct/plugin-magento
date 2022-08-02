<?php

declare(strict_types=1);

namespace Worldline\Payment\Block;

use Magento\Framework\App\Area;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\ConfigurableInfo;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Worldline\Payment\Model\AdditionalInfoInterface;
use Worldline\Payment\Model\Ui\PaymentIconsProvider;
use Worldline\Payment\Model\Ui\PaymentProductsProvider;

class Info extends ConfigurableInfo
{
    public const WORLDLINE = 'worldline';
    public const MAX_HEIGHT = '40';

    private const SKIP_ITEM = 'skip_item';

    /**
     * @var string
     */
    protected $_template = 'Worldline_Payment::info/default.phtml';

    /**
     * @var OrderPaymentInterface|null
     */
    private $payment;

    /**
     * @var PaymentIconsProvider
     */
    private $paymentIconProvider;

    /**
     * @var int
     */
    private $paymentProductId;

    /**
     * @var string[]
     */
    private $keyToLabelMap;

    public function __construct(
        Context $context,
        ConfigInterface $config,
        PaymentIconsProvider $paymentIconProvider,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);
        $this->paymentIconProvider = $paymentIconProvider;
    }

    public function getPaymentTitle(): Phrase
    {
        if ($this->isWorldlinePayment()) {
            $methodUsed = ($this->getPaymentProductId())
                ? PaymentProductsProvider::PAYMENT_PRODUCTS[$this->getPaymentProductId()]['label']
                : 'Payment';

            return __('%1 with Worldline', __($methodUsed)->render());
        } else {
            $methodUsed = $this->getMethod()->getConfigData('title', $this->getInfo()->getOrder()->getStoreId());

            return __($methodUsed);
        }
    }

    public function isWorldlinePayment(): bool
    {
        $payment = $this->getPayment();
        return substr($payment->getMethod(), 0, strlen(self::WORLDLINE)) === self::WORLDLINE;
    }

    public function getSpecificInformation(): array
    {
        $specificInfo = parent::getSpecificInformation();
        $paymentAdditionInfo = [];

        foreach ($this->getPayment()->getAdditionalInformation() as $key => $value) {
            $paymentAdditionInfo[$this->keyToLabel($key)] = $value;
        }

        unset($paymentAdditionInfo[self::SKIP_ITEM]);

        return array_merge($specificInfo, $paymentAdditionInfo);
    }

    public function getIconUrl(): string
    {
        return $this->getIconForType()['url'] ?? '';
    }

    public function getIconWidth(): int
    {
        return $this->getIconForType()['width'];
    }

    public function getIconHeight(): int
    {
        return $this->getIconForType()['height'];
    }

    public function getIconTitle(): Phrase
    {
        return __($this->getIconForType()['title']);
    }

    public function getLast4Digits(): ?string
    {
        $payment = $this->getPayment();
        $last4Digits = $payment->getAdditionalInformation(AdditionalInfoInterface::KEY_CARD_LAST_4);
        return is_string($last4Digits) ? $last4Digits : null;
    }

    public function getAspectRatio(): string
    {
        return $this->getIconWidth() . '/' . $this->getIconHeight();
    }

    public function getMaxHeight(): string
    {
        return self::MAX_HEIGHT . 'px';
    }

    private function getPayment(): OrderPaymentInterface
    {
        if (null === $this->payment) {
            $this->payment = $this->getInfo()->getOrder()->getPayment();
        }

        return $this->payment;
    }

    private function getIconForType(): array
    {
        return $this->paymentIconProvider->getIconById($this->getPaymentProductId());
    }

    private function keyToLabel(string $key): string
    {
        return $this->getKeyToLabelMap()[$key] ?? self::SKIP_ITEM;
    }

    private function getPaymentProductId(): int
    {
        if (empty($this->paymentProductId)) {
            $this->paymentProductId = (int) $this->getPayment()
                ->getAdditionalInformation(AdditionalInfoInterface::KEY_PAYMENT_PRODUCT_ID);
        }

        return $this->paymentProductId;
    }

    private function getKeyToLabelMap(): array
    {
        if (empty($this->keyToLabelMap)) {
            $this->keyToLabelMap = [
                AdditionalInfoInterface::KEY_STATUS =>                 (string) __('Status'),
                AdditionalInfoInterface::KEY_STATUS_CODE =>            (string) __('Status code'),
                AdditionalInfoInterface::KEY_PAYMENT_TRANSACTION_ID => (string) __('Transaction number (payment)'),
                AdditionalInfoInterface::KEY_TOTAL =>                  (string) __('Total'),
                AdditionalInfoInterface::KEY_PAYMENT_METHOD =>         (string) __('Payment method'),
                AdditionalInfoInterface::KEY_FRAUD_RESULT =>           (string) __('Fraud result'),
                AdditionalInfoInterface::KEY_REFUND_TRANSACTION_ID =>  (string) __('Transaction number (refund)'),
                AdditionalInfoInterface::KEY_REFUND_AMOUNT =>          (string) __('Refund amount'),
            ];

            if ($this->getArea() !== Area::AREA_ADMINHTML) {
                unset($this->keyToLabelMap[AdditionalInfoInterface::KEY_STATUS_CODE]);
            }
        }

        return $this->keyToLabelMap;
    }
}
