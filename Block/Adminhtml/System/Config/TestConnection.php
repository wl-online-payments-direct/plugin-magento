<?php

declare(strict_types=1);

namespace Worldline\Payment\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class TestConnection extends Field
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Context $context
     * @param Json $serializer
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        Json $serializer,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        parent::__construct($context, $data, $secureRenderer);
        $this->serializer = $serializer;
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return $this->_decorateRowHtml($element, $this->_getElementHtml($element));
    }

    /**
     * @return TestConnection
     */
    protected function _prepareLayout(): TestConnection
    {
        parent::_prepareLayout();
        $this->setTemplate('Worldline_Payment::config/form/field/testconnection.phtml');
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'label' => __($originalData['label']),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('worldline/system_config/testconnection'),
                'field_mapping' => str_replace('"', '\\"', $this->serializer->serialize($this->_getFieldMapping()))
            ]
        );

        return $this->_toHtml();
    }

    /**
     * @return string[]
     */
    protected function _getFieldMapping()
    {
        return [
            'api_key' => 'worldline_connection_connection_api_key',
            'api_secret' => 'worldline_connection_connection_api_secret',
            'api_test_endpoint' => 'worldline_connection_connection_testing_api_url',
            'api_prod_endpoint' => 'worldline_connection_connection_production_api_url',
            'environment_mode' => 'worldline_connection_connection_environment_mode',
            'merchant_id' => 'worldline_connection_connection_merchant_id'
        ];
    }
}
