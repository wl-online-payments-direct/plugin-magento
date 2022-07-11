<?php

declare(strict_types=1);

namespace Worldline\Payment\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

class RiskDataHandler implements HandlerInterface
{
    /**
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
     */
    public function handle(array $handlingSubject, array $response): void
    {
    }
}
