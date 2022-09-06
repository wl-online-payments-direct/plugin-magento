<?php

declare(strict_types=1);

namespace Worldline\Payment\OnlinePayments\Sdk;

use OnlinePayments\Sdk\CallContext;
use OnlinePayments\Sdk\Communicator as IngenicoCommunicator;
use OnlinePayments\Sdk\CommunicatorConfiguration;
use OnlinePayments\Sdk\Connection;
use OnlinePayments\Sdk\DataObject;
use OnlinePayments\Sdk\RequestObject;
use OnlinePayments\Sdk\ResponseBuilder;
use OnlinePayments\Sdk\ResponseClassMap;
use OnlinePayments\Sdk\ResponseException;
use Worldline\Payment\Logger\RequestLogManager;
use Worldline\Payment\Model\TrackerDataProvider;

/**
 * @core
 *
 * @codingStandardsIgnoreFile
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var RequestLogManager
     */
    private $requestLogManager;

    public function __construct(
        Connection $connection,
        CommunicatorConfiguration $communicatorConfiguration,
        TrackerDataProvider $trackerDataProvider,
        RequestHeaderGeneratorFactory $requestHeaderGeneratorFactory,
        RequestLogManager $requestLogManager
    ) {
        parent::__construct($connection, $communicatorConfiguration);

        $this->trackerDataProvider = $trackerDataProvider;
        $this->communicatorConfiguration = $communicatorConfiguration;
        $this->requestHeaderGeneratorFactory = $requestHeaderGeneratorFactory;
        $this->requestLogManager = $requestLogManager;
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

    /**
     * @see \OnlinePayments\Sdk\Communicator::get()
     *
     * @param ResponseClassMap $responseClassMap
     * @param string $relativeUriPath
     * @param string $clientMetaInfo
     * @param RequestObject|null $requestParameters
     * @param CallContext $callContext
     * @return DataObject
     * @throws ResponseException
     */
    public function get(
        ResponseClassMap $responseClassMap,
        $relativeUriPath,
        $clientMetaInfo = '',
        RequestObject $requestParameters = null,
        CallContext $callContext = null
    ) {
        $relativeUriPathWithRequestParameters = $this->getRelativeUriPathWithRequestParameters($relativeUriPath, $requestParameters);
        $requestHeaders = $this->getRequestHeaders('GET', $relativeUriPathWithRequestParameters, null, $clientMetaInfo, $callContext);

        $responseBuilder = new ResponseBuilder();
        $responseHandler = function ($httpStatusCode, $data, $headers) use ($responseBuilder) {
            $responseBuilder->setHttpStatusCode($httpStatusCode);
            $responseBuilder->setHeaders($headers);
            $responseBuilder->appendBody($data);
        };

        $this->getConnection()->get(
            $this->communicatorConfiguration->getApiEndpoint() . $relativeUriPathWithRequestParameters,
            $requestHeaders,
            $responseHandler,
            $this->communicatorConfiguration->getProxyConfiguration()
        );
        $connectionResponse = $responseBuilder->getResponse();
        $this->updateCallContext($connectionResponse, $callContext);
        $response = $this->getResponseFactory()->createResponse($connectionResponse, $responseClassMap);
        $httpStatusCode = $connectionResponse->getHttpStatusCode();

        $this->requestLogManager->log(
            (string) $relativeUriPathWithRequestParameters,
            (int) $httpStatusCode,
            '',
            (string) $connectionResponse->getBody()
        );

        if ($httpStatusCode >= 400) {
            throw $this->getResponseExceptionFactory()->createException($httpStatusCode, $response, $callContext);
        }
        return $response;
    }

    /**
     * @see \OnlinePayments\Sdk\Communicator::post()
     *
     * @param ResponseClassMap $responseClassMap
     * @param string $relativeUriPath
     * @param string $clientMetaInfo
     * @param DataObject|null $requestBodyObject
     * @param RequestObject|null $requestParameters
     * @param CallContext $callContext
     * @return DataObject
     * @throws Exception
     */
    public function post(
        ResponseClassMap $responseClassMap,
        $relativeUriPath,
        $clientMetaInfo = '',
        $requestBodyObject = null,
        RequestObject $requestParameters = null,
        CallContext $callContext = null
    ) {
        $relativeUriPathWithRequestParameters = $this->getRelativeUriPathWithRequestParameters($relativeUriPath, $requestParameters);
        if ($requestBodyObject instanceof DataObject || is_null($requestBodyObject)) {
            $contentType = static::MIME_APPLICATION_JSON;
            $requestBody = $requestBodyObject ? $requestBodyObject->toJson() : '';
        } else {
            throw new UnexpectedValueException('Unsupported request body');
        }
        $requestHeaders = $this->getRequestHeaders('POST', $relativeUriPathWithRequestParameters, $contentType, $clientMetaInfo, $callContext);

        $responseBuilder = new ResponseBuilder();
        $responseHandler = function ($httpStatusCode, $data, $headers) use ($responseBuilder) {
            $responseBuilder->setHttpStatusCode($httpStatusCode);
            $responseBuilder->setHeaders($headers);
            $responseBuilder->appendBody($data);
        };

        $this->getConnection()->post(
            $this->communicatorConfiguration->getApiEndpoint() . $relativeUriPathWithRequestParameters,
            $requestHeaders,
            $requestBody,
            $responseHandler,
            $this->communicatorConfiguration->getProxyConfiguration()
        );
        $connectionResponse = $responseBuilder->getResponse();
        $this->updateCallContext($connectionResponse, $callContext);
        $response = $this->getResponseFactory()->createResponse($connectionResponse, $responseClassMap);
        $httpStatusCode = $connectionResponse->getHttpStatusCode();

        $this->requestLogManager->log(
            (string) $relativeUriPath,
            (int) $httpStatusCode,
            (string) $requestBody,
            (string) $connectionResponse->getBody()
        );

        if ($httpStatusCode >= 400) {
            throw $this->getResponseExceptionFactory()->createException($httpStatusCode, $response, $callContext);
        }
        return $response;
    }
}
