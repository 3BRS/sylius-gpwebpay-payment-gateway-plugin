<?php

declare(strict_types=1);

namespace MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Model\WebpaySdk;

use RuntimeException;

class Api
{
	/** @var string */
	private $webPayUrl;

	/** @var string */
	private $merchantNumber;

	/** @var Signer */
	private $signer;

	public function __construct(string $merchantNumber, string $webPayUrl, Signer $signer)
	{
		$this->merchantNumber = $merchantNumber;
		$this->webPayUrl = $webPayUrl;
		$this->signer = $signer;
	}

	/**
	 * @param PaymentRequest $request
	 *
	 * @return string
	 */
	public function createPaymentRequestUrl(PaymentRequest $request): string
	{
		// build request URL based on PaymentRequest
		$paymentUrl = $this->webPayUrl . '?' . http_build_query($this->createPaymentParam($request));

		return $paymentUrl;
	}

	/**
	 * @param PaymentRequest $request
	 *
	 * @return array
	 */
	public function createPaymentParam(PaymentRequest $request): array
	{
		// digest request
		$request->setMerchantNumber($this->merchantNumber);
		$params = $request->getParams();
		$request->setDigest($this->signer->sign($params));

		return $request->getParams();
	}

	/**
	 * @param PaymentResponse $response
	 *
	 * @throws RuntimeException
	 * @throws PaymentResponseException
	 */
	public function verifyPaymentResponse(PaymentResponse $response)
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
				'Response has an error.'
			);
		}
	}
}
