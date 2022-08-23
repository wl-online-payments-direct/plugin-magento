<?php

declare(strict_types=1);

namespace Worldline\Payment\Model\HostedCheckout\Order;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;

class EmailSender
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @param LoggerInterface $logger
     * @param OrderSender $orderSender
     * @param InvoiceSender $invoiceSender
     */
    public function __construct(
        LoggerInterface $logger,
        OrderSender $orderSender,
        InvoiceSender $invoiceSender
    ) {
        $this->logger = $logger;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * @param Order $order
     * @return void
     */
    public function send(Order $order)
    {
        try {
            if (!$order->getEmailSent()) {
                $this->orderSender->send($order);
            }
            foreach ($order->getInvoiceCollection() as $invoice) {
                if (!$invoice->getEmailSent()) {
                    $this->invoiceSender->send($invoice);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->critical($e);
        }
    }
}
