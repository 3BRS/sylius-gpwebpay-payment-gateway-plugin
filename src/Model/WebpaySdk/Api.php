<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\WebpaySdk;

use RuntimeException;

readonly class Api
{
    public function __construct(
        private string $merchantNumber,
        private string $webPayUrl,
        private Signer $signer,
    ) {
    }

    public function createPaymentRequestUrl(GpWebPayPaymentRequest $request): string
    {
        return $this->webPayUrl . '?' . http_build_query($this->createPaymentParam($request));
    }

    public function createPaymentPostRequestUrl(): string
    {
        return $this->webPayUrl;
    }

    public function createPaymentParam(GpWebPayPaymentRequest $request): array
    {
        // digest request
        $request->setMerchantNumber($this->merchantNumber);
        $params = $request->getParams();
        $request->setDigest($this->signer->sign($params));

        return $request->getParams();
    }

    /**
     * @throws RuntimeException
     * @throws PaymentResponseException
     */
    public function verifyPaymentResponse(PaymentResponse $response): void
    {
        // verify digest & digest1
        try {
            $responseParams = $response->getParams();
            $this->signer->verify($responseParams, $response->getDigest());

            $responseParams['MERCHANTNUMBER'] = $this->merchantNumber;

            $this->signer->verify($responseParams, $response->getDigest1());
        } catch (SignerException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        // verify PRCODE and SRCODE
        if (false !== $response->hasError()) {
            throw new PaymentResponseException(
                $response->getParams()['prcode'],
                $response->getParams()['srcode'],
                'Response has an error.',
            );
        }
    }
}
