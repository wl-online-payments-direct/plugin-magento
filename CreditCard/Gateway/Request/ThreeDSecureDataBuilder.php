<?php

declare(strict_types=1);

namespace Worldline\Payment\CreditCard\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use OnlinePayments\Sdk\Domain\RedirectionDataFactory;
use OnlinePayments\Sdk\Domain\ThreeDSecure;
use OnlinePayments\Sdk\Domain\ThreeDSecureFactory;
use Worldline\Payment\CreditCard\Gateway\Config\Config;

class ThreeDSecureDataBuilder implements BuilderInterface
{
    public const THREEDSECURE = 'threedsecure';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ThreeDSecureFactory
     */
    private $threeDSecureFactory;

    /**
     * @var RedirectionDataFactory
     */
    private $redirectionDataFactory;

    public function __construct(
        Config $config,
        ThreeDSecureFactory $threeDSecureFactory,
        RedirectionDataFactory $redirectionDataFactory
    ) {
        $this->config = $config;
        $this->threeDSecureFactory = $threeDSecureFactory;
        $this->redirectionDataFactory = $redirectionDataFactory;
    }

    /**
     * @param array $buildSubject
     * @return ThreeDSecure[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(array $buildSubject): array
    {
        $threeDSecure = $this->threeDSecureFactory->create();
        $threeDSecure->setSkipAuthentication($this->config->hasSkipAuthentication());
        $this->addRedirectionData($threeDSecure);

        return [
            self::THREEDSECURE => $threeDSecure
        ];
    }

    private function addRedirectionData(ThreeDSecure $threeDSecure): void
    {
        $redirectionData = $this->redirectionDataFactory->create();
        $redirectionData->setReturnUrl($this->config->getReturnUrl());
        $threeDSecure->setRedirectionData($redirectionData);
    }
}
