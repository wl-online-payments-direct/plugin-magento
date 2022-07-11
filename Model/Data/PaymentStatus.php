<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Data;

use Worldline\Payment\Api\Data\PaymentStatusInterface;

class PaymentStatus implements PaymentStatusInterface
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var int|null
     */
    private $eciCode;

    /**
     * @param string $status
     * @param int $statusCode
     * @param int|null $eciCode
     */
    public function __construct(string $status, int $statusCode, ?int $eciCode = null)
    {
        $this->status = $status;
        $this->statusCode = $statusCode;
        $this->eciCode = $eciCode;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return int|null
     */
    public function getEciCode(): ?int
    {
        return $this->eciCode;
    }
}
