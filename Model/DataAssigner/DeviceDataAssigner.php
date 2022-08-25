<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\DataAssigner;

use Magento\Quote\Api\Data\PaymentInterface;

/**
 * @core
 */
class DeviceDataAssigner implements DataAssignerInterface
{
    public function assign(PaymentInterface $payment, array $additionalInformation): void
    {
        $payment->setAdditionalInformation(
            'device',
            [
                'AcceptHeader' => $additionalInformation['agent'] ?? '',
                'UserAgent' => $additionalInformation['user-agent'] ?? '',
                'Locale' => $additionalInformation['locale'] ?? '',
                'TimezoneOffsetUtcMinutes' => $additionalInformation['TimezoneOffsetUtcMinutes'] ?? '',
                'BrowserData' => [
                    'ColorDepth' => $additionalInformation['ColorDepth'] ?? '',
                    'JavaEnabled' => (bool) ($additionalInformation['JavaEnabled'] ?? false),
                    'ScreenHeight' => $additionalInformation['ScreenHeight'] ?? '',
                    'ScreenWidth' => $additionalInformation['ScreenWidth'] ?? '',
                ],
            ]
        );
    }
}
