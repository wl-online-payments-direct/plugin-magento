<?php

declare(strict_types=1);

namespace Worldline\Payment\Plugin\Model;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\View\Asset\Source;
use Magento\Payment\Model\CcConfigProvider as Origin;
use Worldline\Payment\Model\Ui\PaymentIconsProvider;
use Worldline\Payment\Model\Ui\PaymentProductsProvider;

class CcConfigProvider
{
    /**
     * @var Source
     */
    private $assetSource;

    /**
     * @var PaymentIconsProvider
     */
    private $paymentIconsProvider;

    /**
     * @var DriverInterface
     */
    private $filesystem;

    /**
     * @param Source $assetSource
     * @param PaymentIconsProvider $paymentIconsProvider
     * @param DriverInterface $filesystem
     */
    public function __construct(
        Source $assetSource,
        PaymentIconsProvider $paymentIconsProvider,
        DriverInterface $filesystem
    ) {
        $this->assetSource = $assetSource;
        $this->paymentIconsProvider = $paymentIconsProvider;
        $this->filesystem = $filesystem;
    }

    /**
     * After get icons for available payment methods.
     *
     * @param Origin $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function afterGetIcons(
        Origin $subject,
        array $result
    ): array {
        if (!isset($result['CB'])) {
            $asset = $this->paymentIconsProvider->createAsset('Worldline_Payment::images/cc/pay_cartebancaire.svg');
            $placeholder = $this->assetSource->findSource($asset);
            if ($placeholder) {
                list($width, $height) = getimagesizefromstring(
                    $this->filesystem->fileGetContents($asset->getSourceFile())
                );
                $title = PaymentProductsProvider::PAYMENT_PRODUCTS[130]['label'];
                $result['CB'] = [
                    'url' => $asset->getUrl(),
                    'width' => $width,
                    'height' => $height,
                    'title' => __($title)
                ];
            }
        }

        return $result;
    }
}
