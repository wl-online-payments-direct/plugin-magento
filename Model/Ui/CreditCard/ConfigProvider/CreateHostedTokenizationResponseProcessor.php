<?php

declare(strict_types=1);

namespace  Worldline\Payment\Model\Ui\CreditCard\ConfigProvider;

use Exception;
use Magento\Framework\Locale\Resolver as LocalResolver;
use OnlinePayments\Sdk\Domain\CreateHostedTokenizationRequestFactory;
use OnlinePayments\Sdk\Domain\CreateHostedTokenizationResponse;
use Worldline\Payment\Gateway\Config\Config;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\Customer\ExpiredAndInvalidTokensHandler;
use Worldline\Payment\Model\Customer\VaultCards;
use Worldline\Payment\Model\WorldlineConfig;

class CreateHostedTokenizationResponseProcessor
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var WorldlineConfig
     */
    private $worldlineConfig;

    /**
     * @var ClientProvider
     */
    private $modelClient;

    /**
     * @var VaultCards
     */
    private $vaultCards;

    /**
     * @var ExpiredAndInvalidTokensHandler
     */
    private $expiredAndInvalidTokensHandler;

    /**
     * @var LocalResolver
     */
    private $localResolver;

    /**
     * @var CreateHostedTokenizationRequestFactory
     */
    private $createHostedTokenizationRequestFactory;

    /**
     * @param Config $config
     * @param WorldlineConfig $worldlineConfig
     * @param ClientProvider $modelClient
     * @param VaultCards $vaultCards
     * @param ExpiredAndInvalidTokensHandler $expiredAndInvalidTokensHandler
     * @param LocalResolver $localResolver
     * @param CreateHostedTokenizationRequestFactory $createHostedTokenizationRequestFactory
     */
    public function __construct(
        Config $config,
        WorldlineConfig $worldlineConfig,
        ClientProvider $modelClient,
        VaultCards $vaultCards,
        ExpiredAndInvalidTokensHandler $expiredAndInvalidTokensHandler,
        LocalResolver $localResolver,
        CreateHostedTokenizationRequestFactory $createHostedTokenizationRequestFactory
    ) {
        $this->config = $config;
        $this->worldlineConfig = $worldlineConfig;
        $this->modelClient = $modelClient;
        $this->vaultCards = $vaultCards;
        $this->expiredAndInvalidTokensHandler = $expiredAndInvalidTokensHandler;
        $this->localResolver = $localResolver;
        $this->createHostedTokenizationRequestFactory = $createHostedTokenizationRequestFactory;
    }

    /**
     * @return CreateHostedTokenizationResponse
     * @throws Exception
     */
    public function buildAndProcess(): CreateHostedTokenizationResponse
    {
        $merchantId = $this->worldlineConfig->getMerchantId();

        $createHostedTokenizationRequest = $this->createHostedTokenizationRequestFactory->create();
        $createHostedTokenizationRequest->setAskConsumerConsent(true);
        $createHostedTokenizationRequest->setVariant($this->config->getTemplateId());
        $createHostedTokenizationRequest->setLocale($this->localResolver->getLocale());

        $this->vaultCards->setCurrentCustomerTokens($createHostedTokenizationRequest);
        $createHostedTokenizationResponse = $this->modelClient->getClient()
            ->merchant($merchantId)
            ->hostedTokenization()
            ->createHostedTokenization($createHostedTokenizationRequest);

        $this->expiredAndInvalidTokensHandler->processExpiredAndInvalidTokens(array_merge(
            $createHostedTokenizationResponse->getInvalidTokens(),
            $createHostedTokenizationResponse->getExpiredCardTokens()
        ));

        return $createHostedTokenizationResponse;
    }
}
