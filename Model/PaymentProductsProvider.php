<?php

declare(strict_types=1);

namespace Worldline\Payment\Model;

use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use OnlinePayments\Sdk\Merchant\Products\GetPaymentProductsParams;

class PaymentProductsProvider
{
    public const CACHE_ID = "worldline_payment_products";
    public const CACHE_LIFETIME = 86400;

    protected $configCollection;
    protected $storeManager;
    protected $cache;
    protected $serializer;
    protected $modelClient;
    protected $worldlineConfig;
    protected $scopeConfig;
    protected $configWriter;
    protected $availableProducts = [
        2 => 'americanexpress',
        130 => 'cartebancaire',
        132 => 'dinersclub',
        125 => 'jcb',
        117 => 'maestro',
        3 => 'mastercard',
        1 => 'visa'
    ];

    public function __construct(
        CollectionFactory $configCollection,
        StoreManagerInterface $storeManager,
        CacheInterface $cache,
        SerializerInterface $serializer,
        ClientProvider $modelClient,
        WorldlineConfig $worldlineConfig,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter
    ) {
        $this->configCollection = $configCollection;
        $this->storeManager = $storeManager;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->modelClient = $modelClient;
        $this->worldlineConfig = $worldlineConfig;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
    }

    public function getPaymentProducts($storeId = null)
    {
        $paymentProducts = $this->getPaymentProductsFromCache($storeId);

        if (empty($paymentProducts)) {
            $getParams = new GetPaymentProductsParams();
            $countryCode = $this->scopeConfig->getValue(
                'general/country/default',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $getParams->setCountryCode($countryCode);
            $currencyCode = $this->storeManager->getStore($storeId)->getCurrentCurrency()->getCode();
            $getParams->setCurrencyCode($currencyCode);
            $locale = $this->scopeConfig->getValue(
                'general/locale/code',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $getParams->setLocale($locale);
            try {
                $pPs = $this->modelClient->getClient()
                    ->merchant($this->worldlineConfig->getMerchantId())
                    ->products()
                    ->getPaymentProducts($getParams);
            } catch (\Exception $e) {
                return [];
            }
            $ccTypes = [];
            foreach ($pPs->getPaymentProducts() as $pP) {
                $paymentProducts[$pP->getId()] = [
                    'method' => $pP->getPaymentMethod(),
                    'label' => $pP->getDisplayHints()->getLabel()
                ];
                if (in_array($pP->getId(), array_keys($this->availableProducts))) {
                    $ccTypes[] = $this->availableProducts[$pP->getId()];
                }
            }

            $this->savePaymentProductsToCache($paymentProducts, $storeId);
        }

        return $paymentProducts;
    }

    public function savePaymentProductsToCache($paymentProducts, $storeId)
    {
        $this->cache->save(
            $this->serializer->serialize($paymentProducts),
            self::CACHE_ID . '_' . $storeId,
            [],
            self::CACHE_LIFETIME
        );
    }

    public function getPaymentProductsFromCache($storeId)
    {
        $paymentProducts = $this->cache->load(self::CACHE_ID . '_' . $storeId);
        if (!empty($paymentProducts)) {
            return $this->serializer->unserialize($paymentProducts);
        }
        return [];
    }
}
