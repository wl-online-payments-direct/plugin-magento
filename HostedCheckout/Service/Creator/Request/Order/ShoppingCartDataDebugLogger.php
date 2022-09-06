<?php
declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Service\Creator\Request\Order;

use Magento\Config\Model\Config\Source\Yesno as YesNoOptionProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Tax\Model\Config\Source\Apply\On as ApplyTaxOnOptionProvider;
use Magento\Tax\Model\Config\Source\Basedon as CalcBasedOnOptionProvider;
use Magento\Tax\Model\System\Config\Source\Algorithm as CalcMethodBasedOnOptionProvider;
use Magento\Tax\Model\System\Config\Source\Apply as ApplyCustomerTaxOptionProvider;
use Magento\Tax\Model\System\Config\Source\PriceType as PriceTypeOptionProvider;
use OnlinePayments\Sdk\Domain\ShoppingCart;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShoppingCartDataDebugLogger
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var int
     */
    private $websiteId;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $options;

    public function __construct(
        Logger $logger,
        ScopeConfigInterface $scopeConfig,
        CalcMethodBasedOnOptionProvider $calcMethodBasedOnOptionProvider,
        CalcBasedOnOptionProvider $calcBasedOnOptionProvider,
        PriceTypeOptionProvider $priceTypeOptionProvider,
        ApplyCustomerTaxOptionProvider $applyCustomerTaxOptionProvider,
        ApplyTaxOnOptionProvider $applyTaxOnOptionProvider,
        YesNoOptionProvider $yesNoOptionProvider
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->options = [
            'calcMethodBasedOn' => $calcMethodBasedOnOptionProvider->toOptionArray(),
            'calcBasedOn' => $calcBasedOnOptionProvider->toOptionArray(),
            'priceType' => $priceTypeOptionProvider->toOptionArray(),
            'applyCustomerTax' => $applyCustomerTaxOptionProvider->toOptionArray(),
            'applyTaxOn' => $applyTaxOnOptionProvider->toOptionArray(),
            'yesNo' => $yesNoOptionProvider->toOptionArray()
        ];
    }

    /**
     * @param CartInterface $quote
     * @param ShoppingCart $shoppingCart
     * @return void
     * @throws NoSuchEntityException
     */
    public function log(CartInterface $quote, ShoppingCart $shoppingCart): void
    {
        $this->logger->debug($this->prepareData($quote, $shoppingCart));
    }

    /**
     * @param CartInterface $quote
     * @param ShoppingCart $shoppingCart
     * @return array
     * @throws NoSuchEntityException
     */
    private function prepareData(CartInterface $quote, ShoppingCart $shoppingCart): array
    {
        $website = $quote->getStore()->getWebsite();
        $this->websiteId = (int) $website->getId();

        return [
            'Website' => $website->getName() . ' (code: ' . $website->getCode() . ', id: ' . $this->websiteId . ')',
            'Tax Calculation Settings' => $this->collectTaxCalculationSettings(),
            'Line Items' => $shoppingCart->toJson(),
            'Quote Items' => $this->collectItemsData($quote),
            'Shipping Amount' => $quote->getShippingAddress()->getShippingAmount(),
            'Totals' => [
                'quote.grand_total' => $quote->getGrandTotal(),
                'quote.base_grand_total' => $quote->getBaseGrandTotal(),
                'order.amountOfMoney' => (int) round($quote->getGrandTotal() * 100),
            ]
        ];
    }

    /**
     * @return array
     */
    private function collectTaxCalculationSettings(): array
    {
        return [
            'Tax Calculation Method Based On (' . TaxConfig::XML_PATH_ALGORITHM . ')' =>
                $this->getOptionLabel(
                    $this->options['calcMethodBasedOn'],
                    $this->getConfigValue(TaxConfig::XML_PATH_ALGORITHM)
                ),
            'Tax Calculation Based On (' . TaxConfig::CONFIG_XML_PATH_BASED_ON . ')' =>
                $this->getOptionLabel(
                    $this->options['calcBasedOn'],
                    $this->getConfigValue(TaxConfig::CONFIG_XML_PATH_BASED_ON)
                ),
            'Catalog Prices (' . TaxConfig::CONFIG_XML_PATH_PRICE_INCLUDES_TAX . ')' =>
                $this->getOptionLabel(
                    $this->options['priceType'],
                    (int) $this->getConfigValue(TaxConfig::CONFIG_XML_PATH_PRICE_INCLUDES_TAX)
                ),
            'Shipping Prices (' . TaxConfig::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX . ')' =>
                $this->getOptionLabel(
                    $this->options['priceType'],
                    (int) $this->getConfigValue(TaxConfig::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX)
                ),
            'Apply Customer Tax (' . TaxConfig::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT . ')' =>
                $this->getOptionLabel(
                    $this->options['applyCustomerTax'],
                    (int) $this->getConfigValue(TaxConfig::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT)
                ),
            'Apply Discount On Prices (' . TaxConfig::CONFIG_XML_PATH_DISCOUNT_TAX . ')' =>
                $this->getOptionLabel(
                    $this->options['priceType'],
                    (int) $this->getConfigValue(TaxConfig::CONFIG_XML_PATH_DISCOUNT_TAX)
                ),
            'Apply Tax On (' . TaxConfig::CONFIG_XML_PATH_APPLY_ON . ')' =>
                $this->getOptionLabel(
                    $this->options['applyTaxOn'],
                    (int) $this->getConfigValue(TaxConfig::CONFIG_XML_PATH_APPLY_ON)
                ),
            'Enable Cross Border Trade (' . TaxConfig::CONFIG_XML_PATH_CROSS_BORDER_TRADE_ENABLED . ')' =>
                $this->getOptionLabel(
                    $this->options['yesNo'],
                    (int) $this->getConfigValue(TaxConfig::CONFIG_XML_PATH_CROSS_BORDER_TRADE_ENABLED)
                )
        ];
    }

    /**
     * @param array $options
     * @param int|string $value
     * @return string
     */
    private function getOptionLabel(array $options, $value): string
    {
        foreach ($options as $option) {
            if ($option['value'] === $value) {
                return (string) $option['label'];
            }
        }
    }

    /**
     * @param string $path
     * @return string
     */
    private function getConfigValue(string $path): string
    {
        return (string) $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_WEBSITE,
            $this->websiteId
        );
    }

    /**
     * @param CartInterface $quote
     * @return array
     */
    private function collectItemsData(CartInterface $quote): array
    {
        $data = [];

        foreach ($quote->getItems() as $item) {
            $data[] = $this->collectQuoteItemData($item);
        }

        return $data;
    }

    /**
     * @param CartItemInterface $item
     * @return array
     */
    private function collectQuoteItemData(CartItemInterface $item): array
    {
        return [
            'sku' => $item->getSku(),
            'name' => $item->getName(),
            'price' => $item->getPrice(),
            'base_price' => $item->getBasePrice(),
            'price_incl_tax' => $item->getPriceInclTax(),
            'base_price_incl_tax' => $item->getBasePriceInclTax(),
            'qty' => $item->getQty(),
            'tax_percent' => $item->getTaxPercent(),
            'tax_amount' => $item->getTaxAmount(),
            'base_tax_amount' => $item->getBaseTaxAmount(),
            'row_total' => $item->getRowTotal(),
            'base_row_total' => $item->getBaseRowTotal(),
            'row_total_incl_tax' => $item->getRowTotalInclTax(),
            'base_row_total_incl_tax' => $item->getBaseRowTotalInclTax(),
            'discount_percent' => $item->getDiscountPercent(),
            'discount_amount' => $item->getDiscountAmount(),
            'base_discount_amount' => $item->getBaseDiscountAmount(),
            'discount_tax_compensation_amount' => $item->getDiscountTaxCompensationAmount(),
        ];
    }
}
