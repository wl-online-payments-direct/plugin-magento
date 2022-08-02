<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\Service\Creator\Request\Order;

use OnlinePayments\Sdk\Domain\AmountOfMoney;
use OnlinePayments\Sdk\Domain\AmountOfMoneyFactory;
use Magento\Quote\Api\Data\CartInterface;

class AmountDataBuilder
{
    public const AMOUNT = 'amount';
    public const REFERENCES = 'references';
    public const TOKEN = 'token';

    /**
     * @var AmountOfMoneyFactory
     */
    private $amountOfMoneyFactory;

    public function __construct(
        AmountOfMoneyFactory $amountOfMoneyFactory
    ) {
        $this->amountOfMoneyFactory = $amountOfMoneyFactory;
    }

    public function build(CartInterface $quote): AmountOfMoney
    {
        $amountOfMoney = $this->amountOfMoneyFactory->create();

        $amount = (int)round($quote->getGrandTotal() * 100);

        $amountOfMoney->setAmount($amount);
        $amountOfMoney->setCurrencyCode($quote->getCurrency()->getQuoteCurrencyCode());

        return $amountOfMoney;
    }
}
