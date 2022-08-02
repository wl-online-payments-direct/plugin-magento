<?php
declare(strict_types=1);

namespace Worldline\Payment\Logger\Handler;

use Magento\Framework\Filesystem\Driver\File;
use Monolog\Formatter\LineFormatter;
use Worldline\Payment\Api\Data\LogInterfaceFactory;
use Worldline\Payment\Api\LogRepositoryInterface;
use Worldline\Payment\Model\Log\Log;

class Debug extends \Monolog\Handler\StreamHandler
{
    /**
     * @var File
     */
    private $filesystem;

    /**
     * @var LogRepositoryInterface
     */
    private $logRepository;

    /**
     * @var LogInterfaceFactory
     */
    private $logFactory;

    public function __construct(
        File $filesystem,
        LogRepositoryInterface $logRepository,
        LogInterfaceFactory $logFactory
    ) {
        $this->filesystem = $filesystem;
        parent::__construct(BP . DIRECTORY_SEPARATOR . '/var/log/worldline/debug.log');

        $this->setFormatter(new LineFormatter(null, null, true));
        $this->logRepository = $logRepository;
        $this->logFactory = $logFactory;
    }

    protected function write(array $record): void
    {
        $logDir = $this->filesystem->getParentDirectory($this->url);

        if (!$this->filesystem->isDirectory($logDir)) {
            $this->filesystem->createDirectory($logDir);
        }

        parent::write($record);

        $this->saveLogToDb($record);
    }

    /**
     * @param array $record
     * @return void
     */
    private function saveLogToDb(array $record): void
    {
        $content = var_export($record, true);
        /** @var Log $log */
        $log = $this->logFactory->create();
        $log->setContent($content);
        $this->logRepository->save($log);
    }
}
