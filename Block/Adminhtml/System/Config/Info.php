<?php

declare(strict_types=1);

namespace Worldline\Payment\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Phrase;

class Info extends Field
{
    private const ACCOUNT_LINK = 'https://support.direct.ingenico.com/get-started/account-management/test-environment/';
    private const SALES_LINK = 'https://worldline.com/en/home/solutions/online-payments/wl-online-payments.html';
    private const SUPPORT_LINK = 'https://support.direct.ingenico.com';
    private const DEVELOPER_PORTAL_LINK = 'https://support.direct.ingenico.com/documentation/api/reference/';

    /**
     * @param  AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function render(AbstractElement $element): string
    {
        $label = __(
            '<b>Online payments solutions for every type of business.</b>'
            . '<br />Regardless of the size, markets and scope of your business, we partner with you for growth.'
        );

        return <<<HTML
<tr>
    <td colspan="4">
        <div class="worldline-payment__row">
            <div style="flex: 20%"><img src="{$this->getLogoUrl()}" alt="" /></div>
            <div style="flex: 80%">$label</div>
        </div>
    </td>
</tr>
<tr>
    <td colspan="4">
        <div class="worldline-payment__row">
            <div>
                {$this->getAccountInfo()}
                {$this->getSalesTeamInfo()}
            </div>
        </div>
    </td>
</tr>
<tr>
    <td colspan="4">
        <div class="worldline-payment__row">
            <div>
                {$this->getSupportInfo()}
                {$this->getDeveloperPortalInfo()}
            </div>
        </div>
    </td>
</tr>
HTML;
    }

    /**
     * @return string
     */
    private function getLogoUrl(): string
    {
        return $this->getViewFileUrl(
            'Worldline_Payment::images/logo-config-section.png',
            ['area'  => 'adminhtml']
        );
    }

    /**
     * @return Phrase
     */
    private function getAccountInfo(): Phrase
    {
        return __('<a href="%1" target="_blank">Click here</a> to apply for an account.', self::ACCOUNT_LINK);
    }

    /**
     * @return Phrase
     */
    private function getSalesTeamInfo(): Phrase
    {
        return __(
            'Or contact our <a href="%1" target="_blank">sales team</a> for more details about our products.',
            self::SALES_LINK
        );
    }

    /**
     * @return Phrase
     */
    private function getSupportInfo(): Phrase
    {
        return __(
            'For extension support, please, visit the <a href="%1" target="_blank">support site</a>.',
            self::SUPPORT_LINK
        );
    }

    /**
     * @return Phrase
     */
    private function getDeveloperPortalInfo(): Phrase
    {
        return __(
            'Or visit <a href="%1" target="_blank">developer\'s portal</a> for the API reference and documentation.',
            self::DEVELOPER_PORTAL_LINK
        );
    }
}
