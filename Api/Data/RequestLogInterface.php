<?php

declare(strict_types=1);

namespace Worldline\Payment\Api\Data;

interface RequestLogInterface
{
    public const REQUEST_PATH = 'request_path';
    public const REQUEST_BODY = 'request_body';
    public const RESPONSE_BODY = 'response_body';
    public const RESPONSE_CODE = 'response_code';
    public const CREATED_AT = 'created_at';
    public const MARK_AS_PROCESSED = 'mark_as_processed';

    public function getRequestPath(): ?string;
    public function setRequestPath(string $requestPath): RequestLogInterface;

    public function getRequestBody(): ?string;
    public function setRequestBody(string $requestBody): RequestLogInterface;

    public function getResponseBody(): ?string;
    public function setResponseBody(string $responseBody): RequestLogInterface;

    public function getResponseCode(): ?string;
    public function setResponseCode(int $responseCode): RequestLogInterface;
}
