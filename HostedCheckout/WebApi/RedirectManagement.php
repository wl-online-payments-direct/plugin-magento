<?php

declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\WebApi;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Worldline\Payment\Api\HostedCheckout\RedirectManagementInterface;
use Worldline\Payment\HostedCheckout\Service\Creator\Request;
use Worldline\Payment\HostedCheckout\Service\Creator\RequestBuilder;

class RedirectManagement implements RedirectManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Request
     */
    private $createRequest;

    /**
     * @var RequestBuilder
     */
    private $createRequestBuilder;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        Request $createRequest,
        RequestBuilder $createRequestBuilder,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->cartRepository = $cartRepository;
        $this->createRequest = $createRequest;
        $this->createRequestBuilder = $createRequestBuilder;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * Get redirect url
     *
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @throws LocalizedException
     *
     * @return string redirect url
     */
    public function processRedirectRequest(int $cartId, PaymentInterface $paymentMethod): string
    {
        $quote = $this->cartRepository->get($cartId);

        return $this->process($quote, $paymentMethod);
    }

    /**
     * @param string $cartId
     * @param PaymentInterface $paymentMethod
     * @param string $email
     * @throws LocalizedException
     *
     * @return string redirect url
     */
    public function processGuestRedirectRequest(
        string $cartId,
        PaymentInterface $paymentMethod,
        string $email
    ): string {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quote = $this->cartRepository->get($quoteIdMask->getQuoteId());
        $quote->setCustomerEmail($email);

        // compatibility with magento 2.3.7
        $quote->setCustomerIsGuest(true);

        return $this->process($quote, $paymentMethod);
    }

    private function process(CartInterface $quote, PaymentInterface $paymentMethod): string
    {
        $quote->reserveOrderId();

        $this->setToken($quote, $paymentMethod);

        $request = $this->createRequestBuilder->build($quote);
        $response = $this->createRequest->create($request);
        $quote->getPayment()->setAdditionalInformation('return_id', $response->getRETURNMAC());
        $quote->getPayment()->setAdditionalInformation('hosted_checkout_id', $response->getHostedCheckoutId());

        $this->cartRepository->save($quote);

        return $response->getRedirectUrl();
    }

    private function setToken(CartInterface $quote, PaymentInterface $paymentMethod): void
    {
        $publicToken = $paymentMethod->getAdditionalData()['public_hash'] ?? false;
        if (!$publicToken) {
            $quote->getPayment()->unsAdditionalInformation(PaymentTokenInterface::PUBLIC_HASH);
            $quote->getPayment()->unsAdditionalInformation(PaymentTokenInterface::CUSTOMER_ID);
            return;
        }

        $quote->getPayment()->setAdditionalInformation(PaymentTokenInterface::PUBLIC_HASH, $publicToken);
        $quote->getPayment()->setAdditionalInformation(PaymentTokenInterface::CUSTOMER_ID, $quote->getCustomerId());
    }
}
