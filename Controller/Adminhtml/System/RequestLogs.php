<?php

declare(strict_types=1);

namespace Worldline\Payment\Controller\Adminhtml\System;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class RequestLogs extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Worldline_Payment::request_logs';

    public function execute(): ResultInterface
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->initLayout();
        $this->_setActiveMenu('Worldline_Payment::request_logs');
        $resultPage->getConfig()->getTitle()->prepend(__('Worldline Request Logs'));

        return $resultPage;
    }
}
