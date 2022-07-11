<?php

declare(strict_types=1);

namespace Worldline\Payment\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;

class Failed extends Action
{
    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $this->messageManager->addErrorMessage(__('The payment has failed, please, try again'));

        /**
         * @var Redirect $redirect
         */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setPath('checkout/cart');

        return $redirect;
    }
}
