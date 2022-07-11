<?php

declare(strict_types=1);

namespace Worldline\Payment\Model;

use Exception;
use OnlinePayments\Sdk\Client as IngenicoSdkClient;
use OnlinePayments\Sdk\ClientFactory;
use OnlinePayments\Sdk\CommunicatorConfigurationFactory;
use Worldline\Payment\OnlinePayments\Sdk\Communicator;
use Worldline\Payment\OnlinePayments\Sdk\CommunicatorFactory;

class ClientProvider
{
    /**
     * @var IngenicoSdkClient|null
     */
    private $client;

    /**
     * @var CommunicatorConfigurationFactory
     */
    private $communicatorConfigurationFactory;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var CommunicatorFactory
     */
    private $communicatorFactory;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @param WorldlineConfig $worldlineConfig
     * @param CommunicatorConfigurationFactory $communicatorConfigurationFactory
     * @param CommunicatorFactory $communicatorFactory
     * @param ClientFactory $clientFactory
     */
    public function __construct(
        WorldlineConfig $worldlineConfig,
        CommunicatorConfigurationFactory $communicatorConfigurationFactory,
        CommunicatorFactory $communicatorFactory,
        ClientFactory $clientFactory
    ) {
        $this->worldlineConfig = $worldlineConfig;
        $this->communicatorConfigurationFactory = $communicatorConfigurationFactory;
        $this->communicatorFactory = $communicatorFactory;
        $this->clientFactory = $clientFactory;
    }

    /**
     * @return IngenicoSdkClient
     * @throws Exception
     */
    public function getClient(): IngenicoSdkClient
    {
        if (!$this->client) {
            $this->client = $this->clientFactory->create(['communicator' => $this->getCommunicator()]);
        }

        return $this->client;
    }

    /**
     * @return Communicator
     * @throws Exception
     */
    private function getCommunicator(): Communicator
    {
        $communicatorConfiguration = $this->communicatorConfigurationFactory->create([
            'apiKeyId' => $this->worldlineConfig->getApiKey(),
            'apiSecret' => $this->worldlineConfig->getApiSecret(),
            'apiEndpoint' => $this->worldlineConfig->getApiEndpoint(),
            'integrator' => 'Ingenico',
        ]);

        return $this->communicatorFactory->create(['communicatorConfiguration' => $communicatorConfiguration]);
    }
}
