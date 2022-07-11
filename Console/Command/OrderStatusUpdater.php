<?php

declare(strict_types=1);

namespace Worldline\Payment\Console\Command;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Worldline\Payment\Model\Order\Service\WorldLineApiProcessor;

class OrderStatusUpdater extends Command
{
    /**
     * @var WorldLineApiProcessor
     */
    private $worldLineApiProcessor;

    /**
     * @var State
     */
    private $state;

    /**
     * @param WorldLineApiProcessor $worldLineApiProcessor
     * @param State $state
     * @param string|null $name
     */
    public function __construct(
        WorldLineApiProcessor $worldLineApiProcessor,
        State $state,
        string $name = null
    ) {
        parent::__construct($name);
        $this->worldLineApiProcessor = $worldLineApiProcessor;
        $this->state = $state;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('worldline:update-order-status');
        $this->setDescription('Change status for all orders in processing status or for the given the order id');
        $this->addOption('increment-id', null, InputOption::VALUE_OPTIONAL, 'Order increment id');
        $this->setHelp(<<<EOT
<info>Execute the command to change the status for all orders in pending status</info>
<info>Specify option "--increment-id" to change status for the particular order</info>
EOT
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->state->setAreaCode(Area::AREA_GLOBAL);
            $this->worldLineApiProcessor->process((string) $input->getOption('increment-id'));
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Cli::RETURN_FAILURE;
        }
    }
}
