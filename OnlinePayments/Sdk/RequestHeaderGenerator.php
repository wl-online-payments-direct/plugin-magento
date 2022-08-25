<?php

declare(strict_types=1);

namespace Worldline\Payment\OnlinePayments\Sdk;

use OnlinePayments\Sdk\RequestHeaderGenerator as SdkRequestHeaderGenerator;

/**
 * @core
 */
class RequestHeaderGenerator extends SdkRequestHeaderGenerator
{
    /**
     * @var array
     */
    private $trackerData = [];

    /**
     * @param array $trackerData
     * @return void
     */
    public function setTrackerData(array $trackerData)
    {
        $this->trackerData = $trackerData;
    }

    /**
     * @return string
     */
    protected function getServerMetaInfoValue(): string
    {
        $serverMetaInfo = $this->trackerData;

        $serverMetaInfo['platformIdentifier'] = sprintf('%s; php version %s', php_uname(), phpversion());
        $serverMetaInfo['sdkIdentifier'] = 'PHPServerSDK/v' . static::SDK_VERSION;
        $serverMetaInfo['sdkCreator'] = 'Ingenico';

        $integrator = $this->communicatorConfiguration->getIntegrator();
        if ($integrator) {
            $serverMetaInfo['integrator'] = $integrator;
        }

        $shoppingCartExtension = $this->communicatorConfiguration->getShoppingCartExtension();
        if ($shoppingCartExtension) {
            $serverMetaInfo['shoppingCartExtension'] = $shoppingCartExtension->toObject();
        }

        // the sdkIdentifier contains a /. Without the JSON_UNESCAPED_SLASHES, this is turned to \/ in JSON.
        return base64_encode(json_encode($serverMetaInfo, JSON_UNESCAPED_SLASHES));
    }
}
