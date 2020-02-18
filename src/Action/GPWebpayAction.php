<?php

declare(strict_types=1);

namespace MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Action;

use MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Api\GPWebpayApiInterface;
use MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\SetGPWebpay;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\TokenInterface;

class GPWebpayAction implements ApiAwareInterface, ActionInterface
{
	/** @var GPWebpayApiInterface */
	protected $gpWebPayApi;

	/** @var array */
	private $api = [];

	/**
	 * @param mixed $api
	 */
	public function setApi($api): void
	{
		if (!is_array($api)) {
			throw new UnsupportedApiException('Not supported.');
		}

		$this->api = $api;
	}

	public function __construct(GPWebpayApiInterface $gpWebPayApi)
	{
		$this->gpWebPayApi = $gpWebPayApi;
	}

	/**
	 * @param mixed $request
	 */
	public function execute($request): void
	{
		RequestNotSupportedException::assertSupports($this, $request);
		$model = ArrayObject::ensureArrayObject($request->getModel());

		$sandbox = (bool) $this->api['sandbox'];
		$merchantNumber = (string) $this->api['merchantNumber'];
		$clientPrivateKey = (string) $this->api['keyPrivate'];
		$keyPassword = (string) $this->api['keyPrivatePassword'];
		$preferredPaymentMethod = (string) $this->api['preferredPaymentMethod'];
		$allowedPaymentMethods = (array) $this->api['allowedPaymentMethods'];

		// Not new order
		if ($model['orderId'] !== null) {
			$status = $this->gpWebPayApi->retrieve($merchantNumber, $sandbox, $clientPrivateKey, $keyPassword);
			$model['gpWebPayStatus'] = $status;

			return;
		}

		// New order
		/** @var TokenInterface */
		$token = $request->getToken();
		$order = $this->prepareOrder($token, $model);
		$response = $this->gpWebPayApi->create($order, $merchantNumber, $sandbox, $clientPrivateKey, $keyPassword, $preferredPaymentMethod, $allowedPaymentMethods);

		if ($response) {
			$model['orderId'] = $response['orderId'];
			$request->setModel($model);

			throw new HttpRedirect($response['gatewayLocationUrl']);
		}

		throw new \RuntimeException();
	}

	/**
	 * @param mixed $request
	 */
	public function supports($request): bool
	{
		return
			$request instanceof SetGPWebpay &&
			$request->getModel() instanceof \ArrayObject;
	}

	/**
	 * @param mixed $model
	 *
	 * @return array
	 */
	private function prepareOrder(TokenInterface $token, $model): array
	{
		$order = [];
		$order['currency'] = $model['currencyCode'];
		$order['amount'] = $model['totalAmount'];
		$order['orderNumber'] = $model['number'];
		$order['returnUrl'] = $token->getTargetUrl();

		return $order;
	}
}
