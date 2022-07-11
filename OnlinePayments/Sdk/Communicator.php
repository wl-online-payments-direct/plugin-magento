<?php

declare(strict_types=1);

namespace Worldline\Payment\OnlinePayments\Sdk;

use OnlinePayments\Sdk\CallContext;
use OnlinePayments\Sdk\Communicator as IngenicoCommunicator;
use OnlinePayments\Sdk\CommunicatorConfiguration;
use OnlinePayments\Sdk\Connection;
use OnlinePayments\Sdk\RequestObject;
use Worldline\Payment\Model\TrackerDataProvider;

class Communicator extends IngenicoCommunicator
{
    /**
     * @var TrackerDataProvider
     */
    private $trackerDataProvider;

    /**
     * @var CommunicatorConfiguration
     */
    private $communicatorConfiguration;

    /**
     * @var RequestHeaderGeneratorFactory
     */
    private $requestHeaderGeneratorFactory;

    /**
     * @param Connection $connection
     * @param CommunicatorConfiguration $communicatorConfiguration
     * @param TrackerDataProvider $trackerDataProvider
     * @param RequestHeaderGeneratorFactory $requestHeaderGeneratorFactory
     */
    public function __construct(
        Connection $connection,
        CommunicatorConfiguration $communicatorConfiguration,
        TrackerDataProvider $trackerDataProvider,
        RequestHeaderGeneratorFactory $requestHeaderGeneratorFactory
    ) {
        parent::__construct($connection, $communicatorConfiguration);

        $this->trackerDataProvider = $trackerDataProvider;
        $this->communicatorConfiguration = $communicatorConfiguration;
        $this->requestHeaderGeneratorFactory = $requestHeaderGeneratorFactory;
    }

    /**
     * @param string $httpMethod
     * @param string $relativeUriPathWithRequestParameters
     * @param string $contentType
     * @param string $clientMetaInfo
     * @param CallContext|null $callContext
     *
     * @return string[]
     */
    protected function getRequestHeaders(
        $httpMethod,
        $relativeUriPathWithRequestParameters,
        $contentType,
        $clientMetaInfo = '',
        $callContext = null
    ): array {
        $requestHeaderGenerator = $this->requestHeaderGeneratorFactory->create([
            'communicatorConfiguration' => $this->communicatorConfiguration,
            'httpMethodText' => $httpMethod,
            'uriPath' => $relativeUriPathWithRequestParameters,
            'clientMetaInfo' => $clientMetaInfo,
            'callContext' => $callContext
        ]);

        $requestHeaderGenerator->setTrackerData($this->trackerDataProvider->getData());
        return $requestHeaderGenerator->generateRequestHeaders($contentType);
    }

    public function buildRequestUri(
        string $relativeUriPath,
        ?RequestObject $requestParameters
    ): string {
        return $this->getRequestUri($relativeUriPath, $requestParameters);
    }
}
