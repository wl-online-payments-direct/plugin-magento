<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Worldline\Payment\Gateway\SubjectReader;

class GeneralResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var ErrorCodeProvider
     */
    private $errorCodeProvider;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     * @param ErrorCodeProvider $errorCodeProvider
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader,
        ErrorCodeProvider $errorCodeProvider
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
        $this->errorCodeProvider = $errorCodeProvider;
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $response = $this->subjectReader->readResponseObject($validationSubject);
        $errorMessages = [];
        $errorCodes = $this->errorCodeProvider->getErrorCodes($response);
        return $this->createResult(true, $errorMessages, $errorCodes);
    }
}
