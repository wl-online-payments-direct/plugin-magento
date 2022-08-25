<?php

declare(strict_types=1);

namespace Worldline\Payment\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Worldline\Payment\Model\Config\ConnectionTest\FromAjaxRequest;

/**
 * @core
 */
class TestConnection extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Worldline_Payment::config_worldline';

    /**
     * @var FromAjaxRequest
     */
    private $connectionTester;
    
    public function __construct(
        Context $context,
        FromAjaxRequest $connectionTester
    ) {
        parent::__construct($context);
        
        $this->connectionTester = $connectionTester;
    }
    
    public function execute(): Json
    {
        /** @var Json $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultPage->setData(
            ($errorMessage = $this->connectionTester->test())
                ? ['success' => false, 'errorMessage' => $errorMessage]
                : ['success' => true]
        );
    }
}
