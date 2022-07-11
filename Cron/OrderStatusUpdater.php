<?php

declare(strict_types=1);

namespace Worldline\Payment\Cron;

use Magento\Framework\FlagFactory;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Model\Order\Service\WorldLineApiProcessor;

class OrderStatusUpdater
{
    /**
     * @var WorldLineApiProcessor
     */
    private $worldLineApiProcessor;

    /**
     * @var FlagFactory
     */
    private $flagFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param WorldLineApiProcessor $worldLineApiProcessor
     * @param FlagFactory $flagFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        WorldLineApiProcessor $worldLineApiProcessor,
        FlagFactory $flagFactory,
        LoggerInterface $logger
    ) {
        $this->worldLineApiProcessor = $worldLineApiProcessor;
        $this->flagFactory = $flagFactory;
        $this->logger = $logger;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        $flagModel = $this->flagFactory->create(['data' =>  ['flag_code' => 'world_line_order_update_watcher']]);
        $flagModel->loadSelf();

        if ($flagModel->getFlagData()) {
            return;
        }

        try {
            $flagModel->setFlagData(true)->save();
            $this->worldLineApiProcessor->process();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        } finally {
            $flagModel->setFlagData(false)->save();
        }
    }
}
