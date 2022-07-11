<?php

declare(strict_types=1);

namespace Worldline\Payment\Api\Data;

interface PaymentStatusInterface
{
    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * @return int|null
     */
    public function getEciCode(): ?int;
}
