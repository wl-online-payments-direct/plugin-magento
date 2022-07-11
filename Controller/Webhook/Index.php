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
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Psr\Log\LoggerInterface;
use Worldline\Payment\Webhook\Validator;
use Worldline\Payment\Webhook\WebhookProcessor;

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
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var WebhookProcessor
     */
    private $webhookProcessor;

    /**
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param LoggerInterface $logger
     * @param JsonSerializer $jsonSerializer
     * @param Validator $validator
     * @param WebhookProcessor $webhookProcessor
     */
    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        JsonSerializer $jsonSerializer,
        Validator $validator,
        WebhookProcessor $webhookProcessor
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->jsonSerializer = $jsonSerializer;
        $this->validator = $validator;
        $this->webhookProcessor = $webhookProcessor;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Json $resultPage */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if (!$this->isAuthorized()) {
            $this->logger->info('authorization is not valid');
            return $resultJson;
        }

        $errorMessages = [];
        $this->logger->info('start');
        if ($this->request->isPost()) {
            $this->logger->info('content: ' . $this->request->getContent());
            try {
                $this->webhookProcessor->process($this->jsonSerializer->unserialize($this->request->getContent()));
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

    /**
     * @return bool
     */
    private function isAuthorized(): bool
    {
        return $this->validator->isAuthorized(
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
