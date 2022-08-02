<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\Ui;

use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use OnlinePayments\Sdk\Merchant\Products\GetPaymentProductsParams;
use Worldline\Payment\Model\ClientProvider;
use Worldline\Payment\Model\Config\WorldlineConfig;

class PaymentProductsProvider
{
    public const CACHE_ID = "worldline_payment_products";
    public const CACHE_LIFETIME = 86400; //24h

    /**
     * @link https://support.direct.ingenico.com/en/payment-methods/view-by-payment-product/
     */
    public const PAYMENT_GROUP_MOBILE = 'Mobile';
    public const PAYMENT_GROUP_CARD = 'Cards (debit & credit)';
    public const PAYMENT_GROUP_E_WALLET = 'e-Wallet';
    public const PAYMENT_GROUP_CONSUMER_CREDIT = 'Consumer Credit';
    public const PAYMENT_GROUP_REALTIME_BANKING = 'Real-time banking';
    public const PAYMENT_GROUP_GIFT_CARD = 'Gift card';
    public const PAYMENT_GROUP_INSTALMENT = 'Instalment';
    public const PAYMENT_GROUP_PREPAID = 'Prepaid';
    public const PAYMENT_GROUP_POSTPAID = 'Postpaid';
    public const PAYMENT_GROUP_DIRECT_DEBIT = 'Direct Debit';

    public const PAYMENT_PRODUCTS = [
        1    => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Visa'],
        2    => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'American Express'],
        3    => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Mastercard'],
        117  => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Maestro'],
        125  => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'JCB'],
        130  => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Carte Bancaire'],
        132  => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Diners Club'],
        302  => ['group' => self::PAYMENT_GROUP_MOBILE,           'label' => 'Apple Pay'],
        320  => ['group' => self::PAYMENT_GROUP_MOBILE,           'label' => 'Google Pay'],
        771  => ['group' => self::PAYMENT_GROUP_DIRECT_DEBIT,     'label' => 'SEPA Direct Debit'],
        809  => ['group' => self::PAYMENT_GROUP_REALTIME_BANKING, 'label' => 'iDEAL'],
        840  => ['group' => self::PAYMENT_GROUP_E_WALLET,         'label' => 'Paypal'],
        861  => ['group' => self::PAYMENT_GROUP_MOBILE,           'label' => 'Alipay'],
        863  => ['group' => self::PAYMENT_GROUP_MOBILE,           'label' => 'WeChat Pay'],
        3012 => ['group' => self::PAYMENT_GROUP_CARD,             'label' => 'Bancontact'],
        3112 => ['group' => self::PAYMENT_GROUP_GIFT_CARD,        'label' => 'Illicado'],
        3301 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Klarna'],
        5001 => ['group' => self::PAYMENT_GROUP_E_WALLET,         'label' => 'Bizum'],
        5100 => ['group' => self::PAYMENT_GROUP_CONSUMER_CREDIT,  'label' => 'Cpay'],
        5110 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Oney 3x-4x'],
        5125 => ['group' => self::PAYMENT_GROUP_INSTALMENT,       'label' => 'Oney Financement Long'],
        5402 => ['group' => self::PAYMENT_GROUP_PREPAID,          'label' => 'Mealvouchers'],
        5500 => ['group' => self::PAYMENT_GROUP_POSTPAID,         'label' => 'Multibanco'],
        5600 => ['group' => self::PAYMENT_GROUP_GIFT_CARD,        'label' => 'OneyBrandedGiftCard'],
        5700 => ['group' => self::PAYMENT_GROUP_GIFT_CARD,        'label' => 'Intersolve']
    ];

    /**
     * @var CollectionFactory
     */
    protected $configCollection;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ClientProvider
     */
    protected $modelClient;

    /**
     * @var WorldlineConfig
     */
    protected $worldlineConfig;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var string[]
     */
    protected $availableProducts = [
        1 => 'visa',
        2 => 'americanexpress',
        3 => 'mastercard',
        117 => 'maestro',
        125 => 'jcb',
        130 => 'cartebancaire',
        132 => 'dinersclub'
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
