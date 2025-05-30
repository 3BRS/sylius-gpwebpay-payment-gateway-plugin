<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Provider;

use Psr\Clock\ClockInterface;
use Sylius\Component\Core\Model\PaymentInterface;

class GpWebPayOrderNumberProvider implements GpWebPayOrderNumberProviderInterface
{
    public function __construct(private readonly ClockInterface $clock)
    {
    }

    public function provideOrderNumber(PaymentInterface $payment): string | int
    {
        return ($payment->getOrder()?->getNumber() ?? $payment->getId()) . $this->clock->now()->format('His');
    }
}
