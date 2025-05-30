<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Provider;

use Sylius\Component\Core\Model\PaymentInterface;

interface GpWebPayOrderNumberProviderInterface
{
    /**
     * Provides a unique order number for the given payment.
     *
     * @return string|int The unique order-to-pay number. Type numeric, max length 15, mandatory.
     */
    public function provideOrderNumber(PaymentInterface $payment): string|int;
}
