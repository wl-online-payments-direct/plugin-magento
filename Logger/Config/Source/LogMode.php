<?php

declare(strict_types=1);

namespace Worldline\Payment\Logger\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LogMode implements OptionSourceInterface
{
    public const LOG_ERROR_REQUESTS_ONLY = 0;
    public const LOG_ALL_REQUESTS = 1;

    public function toOptionArray(): array
    {
        return [
            ['value' => self::LOG_ERROR_REQUESTS_ONLY, 'label' => __('Log error requests only')],
            ['value' => self::LOG_ALL_REQUESTS, 'label' => __('Log all requests')]
        ];
    }

    public function toArray(): array
    {
        return [
            self::LOG_ERROR_REQUESTS_ONLY => __('Log error requests only'),
            self::LOG_ALL_REQUESTS => __('Log all requests'),
        ];
    }
}
