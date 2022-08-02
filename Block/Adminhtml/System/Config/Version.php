<?php

declare(strict_types=1);

namespace Worldline\Payment\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Framework\Filesystem\DriverInterface;

class Version extends Field
{
    private const EXTENSION_NAME = 'Worldline_Payment';

    /**
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * @var MagentoComposerApplicationFactory
     */
    private $magentoComposerApplicationFactory;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @param Context $context
     * @param PackageInfo $packageInfo
     * @param MagentoComposerApplicationFactory $magentoComposerApplicationFactory
     * @param DriverInterface $driver
     * @param array $data
     */
    public function __construct(
        Context $context,
        PackageInfo $packageInfo,
        MagentoComposerApplicationFactory $magentoComposerApplicationFactory,
        DriverInterface $driver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->packageInfo = $packageInfo;
        $this->magentoComposerApplicationFactory = $magentoComposerApplicationFactory;
        $this->driver = $driver;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $version = $this->packageInfo->getVersion(self::EXTENSION_NAME);

        $html = '<div class="worldline-payment__row"><div>';
        $html .= __('Current extension version is %1.', $version) . ' ';
        if ($newVersion = $this->getNewVersion()) {
            $html .= __('Latest version available is %1.', $newVersion);
        }

        $html .= '<br />';
        $html .= __(
            'Worldline extension is compatible with Adobe Commerce 2.3.x / 2.4.x & Magento Open Source 2.3.x / 2.4.x.'
        );
        $html .= '</div></div>';

        return '<tr id="row_' . $element->getHtmlId() . '"><td colspan="4">' . $html . '</td></tr>';
    }

    /**
     * @return null|string
     */
    private function getNewVersion(): ?string
    {
        try {
            $packageName = $this->packageInfo->getPackageName(self::EXTENSION_NAME);
            $infoCommand = $this->magentoComposerApplicationFactory->createInfoCommand();
            define('STDIN', $this->driver->fileOpen("php://stdin", "r"));
            $result = $infoCommand->run($packageName);
        } catch (LocalizedException $exception) {
            return null;
        }

        $newVersions = $result['new_versions'] ?? [];
        if (!$newVersions) {
            return null;
        }

        rsort($newVersions);
        $newVersions = array_filter($newVersions, function ($item) {
            return strpos($item, 'dev') === false;
        });

        return current($newVersions) ?? null;
    }
}
