<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Ui;

use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\Source as AssetSource;
use Worldline\Payment\Gateway\Config\Config;
use Worldline\Payment\Model\Config\Source\CreditCardTypeOptions;
use Worldline\Payment\Model\PaymentProductsProvider;

class PaymentIconsProvider
{
    /**
     * @var PaymentProductsProvider
     */
    private $paymentProductsProvider;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var AssetSource
     */
    private $assetSource;

    /**
     * @var array
     */
    private $icons = [];

    /**
     * @var Config
     */
    private $cCconfig;

    /**
     * @var CreditCardTypeOptions
     */
    private $cCoptions;

    /**
     * @param PaymentProductsProvider $paymentProductsProvider
     * @param Repository $assetRepo
     * @param RequestInterface $request
     * @param AssetSource $assetSource
     * @param Config $cCconfig
     * @param CreditCardTypeOptions $cCoptions
     */
    public function __construct(
        PaymentProductsProvider $paymentProductsProvider,
        Repository $assetRepo,
        RequestInterface $request,
        AssetSource $assetSource,
        Config $cCconfig,
        CreditCardTypeOptions $cCoptions
    ) {
        $this->paymentProductsProvider = $paymentProductsProvider;
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->assetSource = $assetSource;
        $this->cCconfig = $cCconfig;
        $this->cCoptions = $cCoptions;
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getIcons(?int $storeId = null): array
    {
        if (!empty($this->icons)) {
            return $this->icons;
        }

        $paymentProducts = $this->paymentProductsProvider->getPaymentProducts($storeId);
        foreach ($paymentProducts as $paymentProductId => $paymentProductData) {
            $asset = $this->createAsset(
                'Worldline_Payment::images/pm/pp_logo_' . $paymentProductId . '.svg',
                [Area::PARAM_AREA => Area::AREA_FRONTEND]
            );
            $placeholder = $this->assetSource->findSource($asset);
            if ($placeholder) {
                list($width, $height) = getimagesize($asset->getSourceFile());
                $this->icons[$paymentProductId] = [
                    'url' => $asset->getUrl(),
                    'width' => $width,
                    'height' => $height,
                    'title' => $paymentProductData['label'],
                    'method' => $paymentProductData['method']
                ];
            }
        }

        return $this->icons;
    }

    /**
     * @param array|null $typesFilter
     * @param int|null $storeId
     * @return array
     */
    public function getFilteredIcons(?array $typesFilter = [], ?int $storeId = null): array
    {
        if (empty($typesFilter)) {
            return $this->getIcons($storeId);
        }

        $icons = [];
        foreach ($this->getIcons($storeId) as $icon) {
            if (in_array($icon['method'], $typesFilter)) {
                $icons[] = $icon;
            }
        }

        return $icons;
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    public function getCcIcons(?int $storeId = null): array
    {
        $cCTypes = explode(',', $this->cCconfig->getCcTypes($storeId));
        if (empty($cCTypes)) {
            return [];
        }

        $icons = [];
        $labels = $this->getCcLabels();
        foreach ($cCTypes as $cCType) {
            $asset = $this->createAsset(
                'Worldline_Payment::images/cc/pay_' . $cCType . '.svg',
                [Area::PARAM_AREA => Area::AREA_FRONTEND]
            );
            $placeholder = $this->assetSource->findSource($asset);
            if ($placeholder) {
                list($width, $height) = getimagesize($asset->getSourceFile());
                $icons[$cCType] = [
                    'url' => $asset->getUrl(),
                    'width' => $width,
                    'height' => $height,
                    'title' => $labels[$cCType]
                ];
            }
        }

        return $icons;
    }

    /**
     * @return array
     */
    public function getCcLabels(): array
    {
        $labels = [];
        foreach ($this->cCoptions->toOptionArray() as $option) {
            $labels[$option['value']] = $option['label'];
        }

        return $labels;
    }

    /**
     * Create a file asset that's subject of fallback system.
     *
     * @param string $fileId
     * @param array $params
     * @return File
     */
    public function createAsset(string $fileId, array $params = []): ?File
    {
        try {
            $params = array_merge(['_secure' => $this->request->isSecure()], $params);
            $result =  $this->assetRepo->createAsset($fileId, $params);
        } catch (LocalizedException $e) {
            $result = null;
        }

        return $result;
    }
}
