<?php

declare(strict_types=1);

namespace Worldline\Payment\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class WebhookInfo extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $elementId = "row_{$element->getHtmlId()}";
        $message = __('To retrieve the webhooks credentials, login to the Worldline Back Office.');
        $message .= '<br>';
        $message .= __(
            'Go to Configuration > Technical information > Ingenico Direct settings > Webhooks Configuration'
        );
        $message .= '<br>';
        $message .= __('and perform the following steps:');

        $step1 = __('Click on "GENERATE WEBHOOKS API KEY"');
        $step2 = __('Copy & Paste the WebhooksKeySecret immediately');
        $step3 = __('In "Endpoints URLs", paste the Webhooks URL of your store - see below');
        $step4 = __('Click on "SAVE" to confirm your settings');

        return <<<HTML
<tr id="{$elementId}">
    <td class="label"></td>
    <td class="value">
        <p class="message message-notification">$message</p>
        <div class="admin__page-nav-item _active">
            <ul style="padding-left: 20px;">
              <li>$step1</li>
              <li>$step2</li>
              <li>$step3</li>
              <li>$step4</li>
            </ul>
        </div>
    </td>
</tr>
HTML;
    }
}
