<?php

declare(strict_types=1);

namespace Worldline\Payment\Controller\Webhook;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Model\Webhook\RequestProcessor;
use Worldline\Payment\Model\Webhook\GeneralProcessor;

class Index implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestProcessor
     */
    private $requestProcessor;

    /**
     * @var GeneralProcessor
     */
    private $webhookProcessor;

    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        RequestProcessor $requestProcessor,
        GeneralProcessor $webhookProcessor
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->requestProcessor = $requestProcessor;
        $this->webhookProcessor = $webhookProcessor;
    }

    public function execute(): ResultInterface
    {
        /** @var Json $resultPage */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $webhookEvent = $this->getWebhookEvent();

        if (!$webhookEvent instanceof WebhooksEvent) {
            $this->logger->info('authorization is not valid');
            return $resultJson;
        }

        $errorMessages = [];
        $this->logger->info('start');
        if ($this->request->isPost()) {
            $this->logger->info('content: ' . $this->request->getContent());
            try {
                $this->webhookProcessor->process($webhookEvent);
            } catch (LocalizedException $exception) {
                $this->logger->error($exception->getMessage());
                $errorMessages = $exception->getMessage();
            }
        } else {
            $this->logger->error(__('Please correct the sent data.'));
            $errorMessages[] = __('Please correct the sent data.');
        }

        $resultJson->setData([
            'messages' => $errorMessages,
            'error' => (bool) $errorMessages,
        ]);

        $this->logger->info('finish');

        return $resultJson;
    }

    private function getWebhookEvent(): ?WebhooksEvent
    {
        return $this->requestProcessor->getWebhookEvent(
            (string) $this->request->getContent(),
            (string) $this->request->getHeader('X-Gcs-Signature'),
            (string) $this->request->getHeader('X-Gcs-Keyid')
        );
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
