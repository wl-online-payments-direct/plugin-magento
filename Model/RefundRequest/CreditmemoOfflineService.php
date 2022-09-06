<?php
declare(strict_types=1);

namespace Worldline\Payment\Model\RefundRequest;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\RefundAdapterInterface;

/**
 * @core
 */
class CreditmemoOfflineService
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var RefundAdapterInterface
     */
    private $refundAdapter;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    public function __construct(
        ResourceConnection $resource,
        RefundAdapterInterface $refundAdapter,
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        $this->resource = $resource;
        $this->refundAdapter = $refundAdapter;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoRepository = $creditmemoRepository;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     * @return CreditmemoInterface
     * @throws LocalizedException
     */
    public function refund(CreditmemoInterface $creditmemo): CreditmemoInterface
    {
        $creditmemo->setState(Creditmemo::STATE_REFUNDED);

        $connection = $this->resource->getConnection('sales');
        $connection->beginTransaction();
        try {
            $invoice = $creditmemo->getInvoice();
            if ($invoice) {
                $invoice->setIsUsedForRefund(true);
                $invoice->setBaseTotalRefunded($invoice->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal());
                $creditmemo->setInvoiceId($invoice->getId());
                $this->invoiceRepository->save($creditmemo->getInvoice());
            }

            $order = $this->refundAdapter->refund($creditmemo, $creditmemo->getOrder());

            $creditmemo->setState(Creditmemo::STATE_REFUNDED);
            $this->creditmemoRepository->save($creditmemo);
            $this->orderRepository->save($order);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new LocalizedException(__($e->getMessage()));
        }

        return $creditmemo;
    }
}
