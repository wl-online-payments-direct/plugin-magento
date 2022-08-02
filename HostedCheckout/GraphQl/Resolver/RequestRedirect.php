<?php
declare(strict_types=1);

namespace Worldline\Payment\HostedCheckout\GraphQl\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\Data\PaymentInterfaceFactory;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Worldline\Payment\Api\HostedCheckout\RedirectManagementInterface;

class RequestRedirect implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var PaymentInterfaceFactory
     */
    private $paymentFactory;

    /**
     * @var RedirectManagementInterface
     */
    private $redirectManagement;

    public function __construct(
        GetCartForUser $getCartForUser,
        PaymentInterfaceFactory $paymentFactory,
        RedirectManagementInterface $redirectManagement
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->paymentFactory = $paymentFactory;
        $this->redirectManagement = $redirectManagement;
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

        if (empty($args['input']['payment_method']['code'])) {
            throw new GraphQlInputException(__('Required parameter "code" for "payment_method" is missing.'));
        }

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        $payment = $this->paymentFactory->create();
        $payment->setMethod($args['input']['payment_method']['code']);

        $redirectUrl = $this->redirectManagement->processRedirectRequest((int)$cart->getId(), $payment);

        return [
            'redirect_url' => $redirectUrl
        ];
    }
}
