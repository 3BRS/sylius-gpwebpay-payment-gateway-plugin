<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\WebpaySdk;

class PaymentResponse
{
    /** @var array */
    protected $params = [];

    /** @var string */
    protected $digest;

    /** @var string */
    protected $digest1;

    public function __construct(string $operation, string $ordernumber, ?string $merordernum, int $prcode, int $srcode, string $resulttext, string $digest, string $digest1)
    {
        $this->params['operation'] = $operation;
        $this->params['ordermumber'] = $ordernumber;
        if ($merordernum !== null) {
            $this->params['merordernum'] = $merordernum;
        }
        $this->params['prcode'] = $prcode;
        $this->params['srcode'] = $srcode;
        $this->params['resulttext'] = $resulttext;
        $this->digest = $digest;
        $this->digest1 = $digest1;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getDigest(): string
    {
        return $this->digest;
    }

    public function hasError(): bool
    {
        return (bool) $this->params['prcode'] || (bool) $this->params['srcode'];
    }

    public function getDigest1(): string
    {
        return $this->digest1;
    }
}
