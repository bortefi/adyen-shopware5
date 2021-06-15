<?php

declare(strict_types=1);

namespace AdyenPayment\Components\Adyen\Mapper;

use AdyenPayment\Models\Payment\PaymentMethod;

class PaymentMethodMapper
{
    /**
     * @param array $data
     * @return \Generator | PaymentMethod[]
     */
    public function mapFromAdyen(array $data)
    {
        if ($data['paymentMethods'] ?? []) {
            foreach ($data['paymentMethods'] as $paymentMethod) {
                yield PaymentMethod::fromRawPaymentData($paymentMethod);
            }
        }

        if ($data['storedPaymentMethods'] ?? []) {
            foreach ($data['storedPaymentMethods'] as $paymentMethod) {
                yield PaymentMethod::fromRawPaymentData($paymentMethod);
            }
        }
    }
}