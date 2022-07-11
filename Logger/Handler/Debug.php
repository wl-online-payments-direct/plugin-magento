<?php
declare(strict_types=1);

namespace Worldline\Payment\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Worldline\Payment\Api\Data\LogInterfaceFactory;
use Worldline\Payment\Api\LogRepositoryInterface;
use Worldline\Payment\Model\Log\Log;

class Debug extends Base
{
    /**
     * @var LogRepositoryInterface
     */
    private $logRepository;

    /**
     * @var LogInterfaceFactory
     */
    private $logFactory;

    public function __construct(
        LogRepositoryInterface $logRepository,
        LogInterfaceFactory $logFactory,
        DriverInterface $filesystem,
        $filePath = null,
        $fileName = null
    ) {
        parent::__construct($filesystem, $filePath, $fileName);

        $this->logRepository = $logRepository;
        $this->logFactory = $logFactory;
    }

    /**
     * @param array $record
     * @return void
     */
    protected function write(array $record): void
    {
        parent::write($record);

        $content = var_export($record, true);
        /** @var Log $log */
        $log = $this->logFactory->create();
        $log->setContent($content);
        $this->logRepository->save($log);
    }
}
