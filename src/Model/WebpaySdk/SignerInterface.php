<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\WebpaySdk;

interface SignerInterface
{
    public function isPrivateKeyAndPasswordValid(): bool;

    /**
     * @throws SignerException
     */
    public function sign(array $params): string;

    /**
     * @throws SignerException
     */
    public function verify(array $params, string $digest): bool;
}
