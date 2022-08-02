<?php

declare(strict_types=1);

namespace Worldline\Payment\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class RequestLogGrid extends Field
{
    public function render(AbstractElement $element): string
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return $this->_decorateRowHtml($element, $this->_getElementHtml($element));
    }

    protected function _prepareLayout(): RequestLogGrid
    {
        $this->setTemplate('Worldline_Payment::config/form/field/request_log_grid.phtml');
        return parent::_prepareLayout();
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'label' => __($originalData['label']),
                'html_id' => $element->getHtmlId(),
                'log_url' => $this->_urlBuilder->getUrl('worldline/system/RequestLogs')
            ]
        );

        return $this->_toHtml();
    }
}
