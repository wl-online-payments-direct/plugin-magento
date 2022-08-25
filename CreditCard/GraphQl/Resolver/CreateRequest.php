<?php
declare(strict_types=1);

namespace Worldline\Payment\CreditCard\GraphQl\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Worldline\Payment\Api\CreditCard\CreatePaymentManagementInterface;

class CreateRequest implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var CreatePaymentManagementInterface
     */
    private $createPaymentManagement;

    public function __construct(
        GetCartForUser $getCartForUser,
        CreatePaymentManagementInterface $createPaymentManagement
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->createPaymentManagement = $createPaymentManagement;
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
        if (!$code) {
            throw new GraphQlInputException(__('Required parameter "code" for "payment_method" is missing.'));
        }

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        $redirectUrl = $this->createPaymentManagement->createRequest((int)$cart->getId(), $cart->getPayment());

        return [
            'redirect_url' => $redirectUrl
        ];
    }
}
