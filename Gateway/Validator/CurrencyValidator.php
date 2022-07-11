<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Validator;

use Exception;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class CurrencyValidator extends AbstractValidator
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param ConfigInterface $config
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ConfigInterface $config
    ) {
        parent::__construct($resultFactory);
        $this->config = $config;
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     * @throws Exception
     */
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
