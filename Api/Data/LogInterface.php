<?php
declare(strict_types=1);

namespace Worldline\Payment\Api\Data;

interface LogInterface
{
    public const LOG_ID = 'log_id';
    public const CONTENT = 'content';
    public const CREATED_AT = 'created_at';

    /**
     * @return int
     */
    public function getLogId(): int;

    /**
     * @return string
     */
    public function getContent(): string;

    /**
     * @param string $content
     * @return void
     */
    public function setContent(string $content): void;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $dateTime
     * @return void
     */
    public function setCreatedAt(string $dateTime): void;
}
