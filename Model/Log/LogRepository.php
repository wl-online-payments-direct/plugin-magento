<?php
declare(strict_types=1);

namespace Worldline\Payment\Model\Log;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Worldline\Payment\Api\Data\LogInterface;
use Worldline\Payment\Api\Data\LogInterfaceFactory;
use Worldline\Payment\Api\LogRepositoryInterface;
use Worldline\Payment\Model\Log\ResourceModel\Log as LogResource;

class LogRepository implements LogRepositoryInterface
{
    /**
     * @var LogInterfaceFactory
     */
    private $logFactory;

    /**
     * @var LogResource
     */
    private $logResource;

    public function __construct(
        LogInterfaceFactory $logFactory,
        LogResource $logResource
    ) {
        $this->logFactory = $logFactory;
        $this->logResource = $logResource;
    }

    /**
     * @param int $logId
     * @return LogInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $logId): LogInterface
    {
        $log = $this->logFactory->create();
        $this->logResource->load($log, $logId);
        if (!$log->getId()) {
            throw new NoSuchEntityException(__('Unable to find Log with ID "%1"', $logId));
        }

        return $log;
    }

    /**
     * @param int $logId
     * @return bool
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function delete(int $logId): bool
    {
        $log = $this->logFactory->create();
        $this->logResource->load($log, $logId);

        if (!$log->getId()) {
            throw new NoSuchEntityException(__('Unable to find Log with ID "%1"', $logId));
        }

        try {
            $this->logResource->delete($log);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot delete Log with id "%1"', $log->getId()), $e);
        }

        return true;
    }

    /**
     * @param LogInterface $log
     * @return LogInterface
     * @throws CouldNotSaveException
     */
    public function save(LogInterface $log): LogInterface
    {
        try {
            $this->logResource->save($log);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Could not save Log: %1', $exception->getMessage()));
        }

        return $log;
    }
}
