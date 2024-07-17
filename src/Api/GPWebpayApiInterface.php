<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Api;

interface GPWebpayApiInterface
{
    public const CREATED = 'CREATED';

    public const PAID = 'PAID';

    public const CANCELED = 'CANCELED';

    public function create(array $order, string $merchantNumber, bool $sandbox, string $keyName, string $keyPassword, ?string $preferredPaymentMethod, ?array $allowedPaymentMethods): array;

    public function retrieve(string $merchantNumber, bool $sandbox, string $keyName, string $keyPassword): string;
}
