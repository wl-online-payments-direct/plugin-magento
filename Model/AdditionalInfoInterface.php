<?php

declare(strict_types=1);

namespace Worldline\Payment\Model;

interface AdditionalInfoInterface
{
    public const KEY_STATUS = 'status';
    public const KEY_STATUS_CODE = 'status_code';
    public const KEY_PAYMENT_TRANSACTION_ID = 'payment_transaction_id';
    public const KEY_REFUND_TRANSACTION_ID = 'refund_transaction_id';
    public const KEY_TOTAL = 'total';
    public const KEY_PAYMENT_METHOD = 'payment_method';
    public const KEY_FRAUD_RESULT = 'fraud_result';
    public const KEY_CARD_LAST_4 = 'card_number';
    public const KEY_PAYMENT_PRODUCT_ID = 'payment_product_id';
    public const KEY_REFUND_AMOUNT = 'refund_amount';
}
