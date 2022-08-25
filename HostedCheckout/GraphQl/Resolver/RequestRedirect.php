<?php
declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\GraphQl\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderPool;
use Worldline\Payment\Api\HostedCheckout\RedirectManagementInterface;

class RequestRedirect implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var RedirectManagementInterface
     */
    private $redirectManagement;

    /**
     * @var AdditionalDataProviderPool
     */
    private $additionalDataProviderPool;

    public function __construct(
        GetCartForUser $getCartForUser,
        RedirectManagementInterface $redirectManagement,
        AdditionalDataProviderPool $additionalDataProviderPool
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->redirectManagement = $redirectManagement;
        $this->additionalDataProviderPool = $additionalDataProviderPool;
    }

    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $maskedCartId = $args['input']['cart_id'];
        if (!$maskedCartId) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing.'));
        }

        $code = $args['input']['payment_method']['code'];
        if (!($code)) {
            throw new GraphQlInputException(__('Required parameter "code" for "payment_method" is missing.'));
        }

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        $payment = $cart->getPayment();

        if ($cart->getCustomerId()) {
            $paymentData = $this->additionalDataProviderPool->getData($code, ['code' => $code]);
            $additionalInfo = array_merge((array)$payment->getAdditionalInformation(), $paymentData);
            $payment->setAdditionalInformation($additionalInfo);
        }

        $redirectUrl = $this->redirectManagement->processRedirectRequest((int)$cart->getId(), $payment);

        return [
            'redirect_url' => $redirectUrl
        ];
    }
}
