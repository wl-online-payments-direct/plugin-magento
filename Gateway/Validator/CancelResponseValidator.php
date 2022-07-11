<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class CancelResponseValidator extends AbstractValidator
{
    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param GeneralResponseValidator $generalResponseValidator
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        GeneralResponseValidator $generalResponseValidator
    ) {
        parent::__construct($resultFactory);
        $this->generalResponseValidator = $generalResponseValidator;
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        return $this->generalResponseValidator->validate($validationSubject);
    }
}
