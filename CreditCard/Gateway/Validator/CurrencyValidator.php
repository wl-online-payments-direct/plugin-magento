<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Gateway\Validator;

use Worldline\Payment\CreditCard\Gateway\Config\Config;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class CurrencyValidator extends AbstractValidator
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(
        ResultInterfaceFactory $resultFactory,
        Config $config
    ) {
        parent::__construct($resultFactory);
        $this->config = $config;
    }

    public function validate(array $validationSubject): ResultInterface
    {
        $isValid = true;
        $storeId = $validationSubject['storeId'];

        if ((int) $this->config->getValue('allow_specific_currency', $storeId) === 1) {
            $availableCurrencies = explode(
                ',',
                $this->config->getValue('currency', $storeId)
            );
            if (!in_array($validationSubject['currency'], $availableCurrencies)) {
                $isValid =  false;
            }
        }

        return $this->createResult($isValid);
    }
}
