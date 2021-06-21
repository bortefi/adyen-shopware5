<?php

declare(strict_types=1);

namespace AdyenPayment\Components\Adyen\PaymentMethod;

use Adyen\AdyenException;
use Adyen\Service\Checkout;
use AdyenPayment\Components\Adyen\ApiFactory;
use AdyenPayment\Components\Configuration;
use Shopware\Models\Shop\Shop;

final class PaymentMethodsProvider
{
    /**
     * @var Configuration
     */
    private $configuration;
    /**
     * @var ApiFactory
     */
    private $adyenApiFactory;

    public function __construct(
        Configuration $configuration,
        ApiFactory $adyenApiFactory
    )
    {
        $this->configuration = $configuration;
        $this->adyenApiFactory = $adyenApiFactory;
    }

    public function __invoke(Shop $shop): array {
        $adyenClient = $this->adyenApiFactory->provide($shop);
        $checkout = new Checkout($adyenClient);

        try {
            $paymentMethods = $checkout->paymentMethods([
                'merchantAccount' => $this->configuration->getMerchantAccount($shop),
            ]);
        } catch (AdyenException $e) {
            // TODO fix exception handling
            return [];
        }

        return $paymentMethods;
    }
}