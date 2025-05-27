<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\WebpaySdk;

class PaymentResponseException extends \Exception
{
    public function __construct(
        private readonly int $prCode,
        private readonly int $srCode = 0,
        string $message = '',
        ?\Exception $previous = null,
    ) {
        parent::__construct($message, $this->prCode, $previous);
    }

    public function getPrCode(): int
    {
        return $this->prCode;
    }

    public function getSrCode(): int
    {
        return $this->srCode;
    }
}
