<?php

declare(strict_types=1);

namespace Worldline\Payment\Controller\Adminhtml\System\Config;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filter\StripTags;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\WorldlineConfig;

class TestConnection extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Worldline_Payment::config_worldline';

    /**
     * @var ClientProvider
     */
    private $clientProvider;

    /**
     * @var StripTags
     */
    private $tagFilter;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @param Context $context
     * @param ClientProvider $clientProvider
     * @param StripTags $tagFilter
     * @param WorldlineConfig $worldlineConfig
     */
    public function __construct(
        Context $context,
        ClientProvider $clientProvider,
        StripTags $tagFilter,
        WorldlineConfig $worldlineConfig
    ) {
        parent::__construct($context);
        $this->clientProvider = $clientProvider;
        $this->tagFilter = $tagFilter;
        $this->worldlineConfig = $worldlineConfig;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        /** @var Json $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $this->initConfigParameters();
            $this->clientProvider->getClient()
                ->merchant($this->worldlineConfig->getMerchantId())
                ->services()
                ->testConnection();
            return $resultPage->setData(['success' => true]);
        } catch (Exception $e) {
            return $resultPage->setData([
                'success' => false,
                'errorMessage' => $this->tagFilter->filter($e->getMessage())
            ]);
        }
    }

    /**
     * @return void
     */
    private function initConfigParameters()
    {
        $this->worldlineConfig->setApiEndpoint($this->getEndpoint());

        $merchantId = $this->getRequest()->getParam('merchant_id');
        $this->worldlineConfig->setMerchantId($merchantId);

        $apiKey = trim($this->getRequest()->getParam('api_key'));
        if (!preg_match('/^[\*]+$/', $apiKey)) {
            $this->worldlineConfig->setApiKey($apiKey);
        }

        $apiSecret = trim($this->getRequest()->getParam('api_secret'));
        if (!preg_match('/^[\*]+$/', $apiSecret)) {
            $this->worldlineConfig->setApiSecret($apiSecret);
        }
    }

    /**
     * @return string
     */
    private function getEndpoint(): string
    {
        $environmentMode = (bool) $this->getRequest()->getParam('environment_mode');
        if ($environmentMode) {
            return (string) $this->getRequest()->getParam('api_prod_endpoint');
        }

        return (string) $this->getRequest()->getParam('api_test_endpoint');
    }
}
