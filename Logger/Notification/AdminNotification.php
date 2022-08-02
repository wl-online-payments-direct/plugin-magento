<?php

declare(strict_types=1);

namespace Worldline\Payment\Logger\Notification;

use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;
use Worldline\Payment\Logger\ResourceModel\RequestLog;

class AdminNotification implements MessageInterface
{
    /**
     * @var RequestLog
     */
    private $requestLog;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(RequestLog $requestLog, UrlInterface $urlBuilder)
    {
        $this->requestLog = $requestLog;
        $this->urlBuilder = $urlBuilder;
    }

    public function getIdentity(): string
    {
        return 'worldline_request_error_notification';
    }

    public function isDisplayed(): bool
    {
        return $this->requestLog->hasErrorRequests();
    }

    public function getText(): string
    {
        $url = $this->urlBuilder->getUrl('worldline/system/RequestLogs');

        $message = __('Worldline payment methods require your attention.');
        $message .= ' ';
        $message .= __('Go to <a href="%1">log grid</a> to see the details', $url);

        return $message;
    }

    public function getSeverity(): int
    {
        return self::SEVERITY_CRITICAL;
    }
}
