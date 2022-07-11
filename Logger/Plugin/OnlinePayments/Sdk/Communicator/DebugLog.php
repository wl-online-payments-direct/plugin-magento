<?php
declare(strict_types=1);

namespace Worldline\Payment\Logger\Plugin\OnlinePayments\Sdk\Communicator;

use Magento\Payment\Model\Method\Logger;
use OnlinePayments\Sdk\CallContext;
use OnlinePayments\Sdk\Communicator;
use OnlinePayments\Sdk\DataObject;
use OnlinePayments\Sdk\RequestObject;
use OnlinePayments\Sdk\ResponseClassMap;

class DebugLog
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Communicator $subject
     * @param callable $proceed
     * @param ResponseClassMap $responseClassMap
     * @param string $relativeUriPath
     * @param string $clientMetaInfo
     * @param DataObject|null $requestBodyObject
     * @param RequestObject|null $requestParameters
     * @param CallContext|null $callContext
     * @return DataObject|null
     * @throws \Exception
     */
    public function aroundPost(
        Communicator $subject,
        callable $proceed,
        ResponseClassMap $responseClassMap,
        string $relativeUriPath,
        string $clientMetaInfo = '',
        DataObject $requestBodyObject = null,
        RequestObject $requestParameters = null,
        CallContext $callContext = null
    ): ?DataObject {
        $data = [
            'requestUri' => $subject->buildRequestUri($relativeUriPath, $requestParameters),
            'requestBody' => $requestBodyObject ? ("\n" . $requestBodyObject->toJson() . "\n") : ''
        ];

        try {
            $response = $proceed(
                $responseClassMap,
                $relativeUriPath,
                $clientMetaInfo,
                $requestBodyObject,
                $requestParameters,
                $callContext
            );
            $data['response'] = $response ? ("\n" . $response->toJson() . "\n") : '';

            return $response;
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();

            throw $e;
        } finally {
            $this->logger->debug($data);
        }
    }
}
