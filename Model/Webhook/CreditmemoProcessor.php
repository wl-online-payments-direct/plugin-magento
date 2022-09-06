<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Webhook;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Helper\Data as SalesData;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Worldline\Payment\Api\RefundRequestRepositoryInterface;
use Worldline\Payment\Api\TransactionWebhookManagerInterface;
use Worldline\Payment\Api\WebhookProcessorInterface;
use Worldline\Payment\Model\RefundRequest\CreditmemoOfflineService;

/**
 * @core
 */
class CreditmemoProcessor implements ProcessorInterface
{
    public const REFUND_CODE = 8;

    /**
     * @var CreditmemoOfflineService
     */
    private $refundOfflineService;

    /**
     * @var RefundRequestRepositoryInterface
     */
    private $refundRequestRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var TransactionWebhookManagerInterface
     */
    private $transactionWebhookManager;

    /**
     * @var SalesData
     */
    private $salesData;

    /**
     * @var CreditmemoSender
     */
    private $creditmemoSender;

    public function __construct(
        CreditmemoOfflineService $refundOfflineService,
        RefundRequestRepositoryInterface $refundRequestRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        TransactionWebhookManagerInterface $transactionWebhookManager,
        SalesData $salesData,
        CreditmemoSender $creditmemoSender
    ) {
        $this->refundOfflineService = $refundOfflineService;
        $this->refundRequestRepository = $refundRequestRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->transactionWebhookManager = $transactionWebhookManager;
        $this->salesData = $salesData;
        $this->creditmemoSender = $creditmemoSender;
    }

    /**
     * @param WebhooksEvent $webhookEvent
     * @return void
     * @throws LocalizedException
     */
    public function process(WebhooksEvent $webhookEvent): void
    {
        $this->transactionWebhookManager->saveTransaction($webhookEvent);

        $statusCode = (int)$webhookEvent->getRefund()->getStatusOutput()->getStatusCode();
        if ($statusCode !== self::REFUND_CODE) {
            return;
        }

        $incrementId = (string)$webhookEvent->getRefund()->getRefundOutput()->getReferences()->getMerchantReference();
        $amount = (int)$webhookEvent->getRefund()->getRefundOutput()->getAmountOfMoney()->getAmount();
        $refundRequest = $this->refundRequestRepository->getByIncrementIdAndAmount($incrementId, $amount);
        if (!$refundRequest->getCreditMemoId()) {
            return;
        }

        $creditmemoEntity = $this->creditmemoRepository->get($refundRequest->getCreditMemoId());

        $this->refundOfflineService->refund($creditmemoEntity);

        $refundRequest->setRefunded(true);
        $this->refundRequestRepository->save($refundRequest);

        $this->notifyCustomer($creditmemoEntity);
    }

    private function notifyCustomer(Creditmemo $creditmemo): void
    {
        if ($creditmemo->getOrder()->getCustomerNoteNotify() && $this->salesData->canSendNewCreditmemoEmail()) {
            $this->creditmemoSender->send($creditmemo);
        }
    }
}
