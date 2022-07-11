<?php

declare(strict_types=1);

namespace Worldline\Payment\GraphQl\Model\Resolver;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;

class Redirect implements ResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws InputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $orderIncrementId = $args['incrementId'];
        if (empty($orderIncrementId)) {
            return [];
        }

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $orderIncrementId)->create();
        $order = $this->orderRepository->getList($searchCriteria)->getFirstItem();
        $payment = $order->getPayment();

        if (empty($payment)) {
            return [];
        }

        if ($payment->getCcStatusDescription() !== 'REDIRECTED') {
            return [];
        }

        return [
            'url' => $payment->getAdditionalInformation('redirectURL')
        ];
    }
}
