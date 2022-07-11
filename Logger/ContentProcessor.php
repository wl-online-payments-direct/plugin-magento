<?php
declare(strict_types=1);

namespace Worldline\Payment\Logger;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Helper\File\Storage;

class ContentProcessor
{
    public const FILENAME = 'worldline/debug.log';

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Storage $storage
     * @param Filesystem $filesystem
     */
    public function __construct(
        Storage $storage,
        Filesystem $filesystem
    ) {
        $this->storage = $storage;
        $this->filesystem = $filesystem;
    }

    /**
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function process(): \Magento\Framework\DataObject
    {
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::LOG);
        $path = $directory->getAbsolutePath(self::FILENAME);
        if (mb_strpos($path, '..') !== false
            || (!$directory->isFile(self::FILENAME) && !$this->storage->processStorageFile($path))
        ) {
            return $this->getEmptyResultObject();
        }

        $stat = $directory->stat(self::FILENAME);
        $contentLength = $stat['size'];
        $contentModify = $stat['mtime'];
        $content = $directory->readFile(self::FILENAME);

        $resultObject = $this->getEmptyResultObject();

        return $resultObject->setContent($content)
            ->setContentLength($contentLength)
            ->setContentModify($contentModify);
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    private function getEmptyResultObject(): \Magento\Framework\DataObject
    {
        $resultObject = new \Magento\Framework\DataObject();
        return $resultObject->setContent('')
            ->setContentLength(0)
            ->setContentModify(time());
    }
}
