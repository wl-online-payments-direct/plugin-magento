<?php

declare(strict_types=1);

namespace Worldline\Payment\Block;

use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\ConfigurableInfo;
use Magento\Payment\Gateway\ConfigInterface;
use Worldline\Payment\Api\Data\PaymentInfoInterface;
use Worldline\Payment\Api\InfoFormatterInterface;
use Worldline\Payment\Model\Transaction\PaymentInfoBuilder;
use Worldline\Payment\Model\Ui\PaymentIconsProvider;
use Worldline\Payment\Model\Ui\PaymentProductsProvider;

class Info extends ConfigurableInfo
{
    public const MAX_HEIGHT = '40px';

    /**
     * @var PaymentIconsProvider
     */
    private $paymentIconProvider;

    /**
     * @var PaymentInfoBuilder
     */
    private $paymentInfoBuilder;

    /**
     * @var InfoFormatterInterface
     */
    private $infoFormatter;

    /**
     * @var PaymentInfoInterface
     */
    private $paymentInformation;

    public function __construct(
        Context $context,
        ConfigInterface $config,
        PaymentIconsProvider $paymentIconProvider,
        PaymentInfoBuilder $paymentInfoBuilder,
        InfoFormatterInterface $infoFormatter,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);
        $this->paymentIconProvider = $paymentIconProvider;
        $this->paymentInfoBuilder = $paymentInfoBuilder;
        $this->infoFormatter = $infoFormatter;
    }

    public function getTemplate(): string
    {
        return 'Worldline_Payment::info/default.phtml';
    }

    public function getSpecificInformation(): array
    {
        return $this->infoFormatter->format($this->getPaymentInformation());
    }

    public function getPaymentTitle(): string
    {
        $paymentProductId = $this->getPaymentInformation()->getPaymentProductId();
        $methodUsed = ($paymentProductId)
            ? PaymentProductsProvider::PAYMENT_PRODUCTS[$paymentProductId]['label']
            : __('Payment');

        return __('%1 with Worldline', $methodUsed)->render();
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

    public function getAspectRatio(): string
    {
        return $this->getIconWidth() . '/' . $this->getIconHeight();
    }

    public function getMaxHeight(): string
    {
        return self::MAX_HEIGHT;
    }

    private function getIconForType(): array
    {
        return $this->paymentIconProvider->getIconById($this->getPaymentInformation()->getPaymentProductId());
    }

    public function getPaymentInformation(): PaymentInfoInterface
    {
        if (null === $this->paymentInformation) {
            $this->paymentInformation = $this->paymentInfoBuilder->build($this->getInfo()->getOrder());
        }

        return $this->paymentInformation;
    }
}
