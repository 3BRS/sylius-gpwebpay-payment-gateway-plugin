<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\WebpaySdk;

class PaymentResponseException extends \Exception
{
    /** @var int */
    private $prCode;

    /** @var int */
    private $srCode;

    public function __construct(int $prCode, int $srCode = 0, string $message = '', \Exception $previous = null)
    {
        $this->prCode = $prCode;
        $this->srCode = $srCode;

        parent::__construct($message, $prCode, $previous);
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
