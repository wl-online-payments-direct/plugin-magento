<?php

declare(strict_types=1);

namespace Worldline\Payment\Controller\Adminhtml\System;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Worldline\Payment\Logger\ResourceModel\RequestLog\CollectionFactory;
use Worldline\Payment\Logger\ResourceModel\RequestLog as RequestLogResource;

class MassProcessed extends Action implements HttpPostActionInterface
{
    /**
     * MassActions filter
     *
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var RequestLogResource
     */
    private $requestLogResource;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        RequestLogResource $requestLogResource
    ) {
        parent::__construct($context);

        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->requestLogResource = $requestLogResource;
    }

    public function execute(): ResultInterface
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $itemIds = $collection->getAllIds();

        try {
            $this->requestLogResource->changeStatus($itemIds, (int) $this->getRequest()->getParam('status'));
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been updated.', count($itemIds))
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while updating log.')
            );
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('worldline/system/RequestLogs');
    }
}
