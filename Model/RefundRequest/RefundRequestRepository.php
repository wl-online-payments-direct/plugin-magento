<?php
declare(strict_types=1);

namespace Worldline\Payment\Model\RefundRequest;

use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Worldline\Payment\Api\Data\RefundRequestInterface;
use Worldline\Payment\Api\Data\RefundRequestInterfaceFactory;
use Worldline\Payment\Api\RefundRequestRepositoryInterface;
use Worldline\Payment\Model\RefundRequest\ResourceModel\RefundRequest as RefundRequestResource;
use Worldline\Payment\Model\RefundRequest\ResourceModel\RefundRequest\Collection;
use Worldline\Payment\Model\RefundRequest\ResourceModel\RefundRequest\CollectionFactory;

/**
 * @core
 */
class RefundRequestRepository implements RefundRequestRepositoryInterface
{
    /**
     * @var RefundRequestResource
     */
    private $refundRequestResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        RefundRequestResource $refundRequestResource,
        CollectionFactory $collectionFactory
    ) {
        $this->refundRequestResource = $refundRequestResource;
        $this->collectionFactory = $collectionFactory;
    }

    public function getListByIncrementId(string $incrementId): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(RefundRequestInterface::INCREMENT_ID, ['eq' => $incrementId]);

        return $collection->getItems();
    }

    public function getByIncrementIdAndAmount(string $incrementId, int $amount): RefundRequestInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(RefundRequestInterface::INCREMENT_ID, ['eq' => $incrementId]);
        $collection->addFieldToFilter(RefundRequestInterface::AMOUNT, ['eq' => $amount]);
        $collection->addFieldToFilter(RefundRequestInterface::REFUNDED, ['eq' => 0]);
        $collection->getSelect()->limit(1);

        return $collection->getFirstItem();
    }

    /**
     * @param RefundRequestInterface $refundRequest
     * @return RefundRequestInterface
     * @throws CouldNotSaveException
     */
    public function save(RefundRequestInterface $refundRequest): RefundRequestInterface
    {
        try {
            $this->refundRequestResource->save($refundRequest);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__('Could not save request to refund: %1', $exception->getMessage()));
        }

        return $refundRequest;
    }
}
