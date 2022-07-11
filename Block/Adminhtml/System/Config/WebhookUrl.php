<?php

declare(strict_types=1);

namespace Worldline\Payment\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class WebhookUrl extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $webhookUrl = $this->getBaseUrl() . 'worldline/webhook';
        $label = __('Webhook URL');
        $comment = __('This URL must be specified in the worldline admin panel');
        $elementId = "row_{$element->getHtmlId()}";
        $copyButtonLabel = __('Copy');

        return <<<HTML
<tr id="{$elementId}">
    <td class="label">
        <label for="{$elementId}">
            <span data-config-scope="[GLOBAL]">{$label}</span>
        </label>
    </td>
    <td class="value">
        <input disabled="disabled" value="{$webhookUrl}" type="text" style="float:left; width: 80%;">
        <button style="float:left;"
                onclick="navigator.clipboard.writeText('{$webhookUrl}');"
                type="button">{$copyButtonLabel}</button>
        <br><br>
        <p class="note"><span>{$comment}</span></p>
    </td>
</tr>
HTML;
    }
}
