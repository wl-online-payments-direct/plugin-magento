<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Ui;

use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\Source as AssetSource;
use Worldline\Payment\CreditCard\Gateway\Config\Config;
use Worldline\Payment\Model\Config\Source\CreditCardTypeOptions;

class PaymentIconsProvider
{
    public const REGEXP_ATTR_VIEWBOX =
        '/viewBox=[\'"](?<startX>\d+) (?<startY>\d+) (?<width>[\d\.]+) (?<height>[\d\.]+)[\'"]/i';

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

    public function getIcons(?int $storeId = null): array
    {
        if (!empty($this->icons)) {
            return $this->icons;
        }

        $paymentProducts = $this->paymentProductsProvider->getPaymentProducts($storeId);
        foreach ($paymentProducts as $paymentProductId => $paymentProductData) {
            $this->generateIconById($paymentProductId, $storeId, $paymentProductData);
        }

        return $this->icons;
    }

    public function getIconById(?int $id, ?int $storeId = null): array
    {
        if (empty($this->getIcons()[$id])) {
            $this->generateIconById($id, $storeId);
        }

        return $this->icons[$id] ?? [];
    }

    private function generateIconById(?int $id, ?int $storeId = null, ?array $data = null): void
    {
        if (empty($data)) {
            $data = $this->paymentProductsProvider->getPaymentProducts($storeId)[$id] ?? [];
        }

        $asset = $this->createAsset(
            'Worldline_Payment::images/pm/pp_logo_' . $id . '.svg',
            [Area::PARAM_AREA => Area::AREA_FRONTEND]
        );
        $placeholder = $this->assetSource->findSource($asset);
        if ($placeholder) {
            list($width, $height) = $this->getDimensions($asset);
            $this->icons[$id] = [
                'url' => $asset->getUrl(),
                'width' => $width,
                'height' => $height,
                'title' => $data['label'],
                'method' => $data['method']
            ];
        }
    }

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
                list($width, $height) = $this->getDimensions($asset);
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

    private function getDimensions(?File $asset = null): array
    {
        if ($asset === null) {
            return [0, 0];
        }

        if ($this->isSvg($asset)) {
            preg_match(self::REGEXP_ATTR_VIEWBOX, $asset->getContent(), $viewBox);
            $width = (int) $viewBox['width'];
            $height = (int) $viewBox['height'];
        } else {
            $size = getimagesizefromstring($asset->getContent());
            $width = (int) $size[0];
            $height = (int) $size[1];
        }

        return [$width, $height];
    }

    private function isSvg(File $asset): bool
    {
        return (bool)preg_match('/\.svg$/i', $asset->getSourceFile());
    }
}
