<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Http\Client;

use Exception;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractTransaction implements ClientInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     * @throws ClientException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        try {
            $response['object'] = $this->process($transferObject->getBody());
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new ClientException(__('Sorry, but something went wrong'));
        }

        return $response;
    }

    /**
     * Process http request
     * @param array $data
     */
    abstract protected function process(array $data);
}
