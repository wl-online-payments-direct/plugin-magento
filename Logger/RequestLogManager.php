<?php

declare(strict_types=1);

namespace Worldline\Payment\Logger;

use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Api\Data\RequestLogInterfaceFactory;
use Worldline\Payment\Logger\Config\ConfigDebugProvider;
use Worldline\Payment\Logger\Config\Source\LogMode;
use Worldline\Payment\Logger\ResourceModel\RequestLog as RequestLogResource;

class RequestLogManager
{
    /**
     * @var RequestLogInterfaceFactory
     */
    private $requestLogFactory;

    /**
     * @var RequestLogResource
     */
    private $requestLogResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigDebugProvider
     */
    private $configDebugProvider;

    public function __construct(
        RequestLogInterfaceFactory $requestLogFactory,
        RequestLogResource $requestLogResource,
        LoggerInterface $logger,
        ConfigDebugProvider $configDebugProvider
    ) {
        $this->requestLogFactory = $requestLogFactory;
        $this->requestLogResource = $requestLogResource;
        $this->logger = $logger;
        $this->configDebugProvider = $configDebugProvider;
    }

    public function log(
        string $relativeUriPath,
        int $responseCode,
        string $requestBody = '',
        string $responseBody = ''
    ): void {
        if (($this->configDebugProvider->getLogMode() === LogMode::LOG_ERROR_REQUESTS_ONLY)
            && $responseCode < 400
        ) {
            return;
        }

        $logRequest = $this->requestLogFactory->create();

        $logRequest->setRequestPath($relativeUriPath);
        $logRequest->setRequestBody($requestBody);
        $logRequest->setResponseBody((string) str_replace([':"', ',"'], [': "', ', "'], $responseBody));
        $logRequest->setResponseCode($responseCode);

        try {
            $this->requestLogResource->save($logRequest);
        } catch (LocalizedException $exception) {
            $this->logger->error($exception->getMessage(), $exception->getTrace());
        }
    }
}
