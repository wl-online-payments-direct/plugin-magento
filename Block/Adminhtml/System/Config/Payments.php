<?php

declare(strict_types=1);

namespace Worldline\Payment\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Payments extends Field
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Return info block html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $worldLineConfigSectionUrl =
            $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/worldline_general');
        $html = '<div class="worldline-payment__row"><div>';
        $html .= __('Worldline Online Payments configuration is located under the dedicated configuration section.');
        $html .= ' ';
        $html .= __('Please, <a href="%1">click here</a> to proceed.', $worldLineConfigSectionUrl);
        $html .= '</div></div>';

        return '<tr id="row_' . $element->getHtmlId() . '">' . '<td colspan="4">' . $html . '</td></tr>';
    }
}
