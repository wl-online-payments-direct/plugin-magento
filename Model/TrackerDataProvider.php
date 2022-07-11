<?php

declare(strict_types=1);

namespace Worldline\Payment\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Serialize\Serializer\Json as Serializer;

class TrackerDataProvider
{
    private const EXTENSION_NAME = 'Worldline_Payment';

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var File
     */
    private $filesystem;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * @param Reader $moduleReader
     * @param File $filesystem
     * @param Serializer $serializer
     * @param PackageInfo $packageInfo
     */
    public function __construct(
        Reader $moduleReader,
        File $filesystem,
        Serializer $serializer,
        PackageInfo $packageInfo
    ) {
        $this->moduleReader = $moduleReader;
        $this->filesystem = $filesystem;
        $this->serializer = $serializer;
        $this->packageInfo = $packageInfo;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        try {
            $file = $this->moduleReader->getModuleDir('', self::EXTENSION_NAME) . '/composer.json';
            $content = $this->filesystem->fileGetContents($file);
            $data = $this->serializer->unserialize($content);
            $result = $data['tracker'] ?? [];
            $result['version'] = $this->packageInfo->getVersion(self::EXTENSION_NAME);
            return $result;
        } catch (FileSystemException $e) {
            return [];
        }
    }
}
