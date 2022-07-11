<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Validator;

class ErrorCodeProvider
{
    /**
     * Retrieves list of error codes from response.
     *
     * @param $response
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getErrorCodes($response): array
    {
        return [];
    }
}
