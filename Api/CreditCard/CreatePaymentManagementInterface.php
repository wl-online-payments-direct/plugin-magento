<?php

declare(strict_types=1);

namespace Worldline\Payment\Api\CreditCard;

interface CreatePaymentManagementInterface
{
    /**
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return string redirect url
     */
    public function createRequest(
        int $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
    ): string;

    /**
     * @param string $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param string $email
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return string redirect url
     */
    public function createGuestRequest(
        string $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        string $email
    ): string;
}
