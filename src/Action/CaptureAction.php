<?php

declare(strict_types=1);

namespace MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Action;

use MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\SetGPWebpay;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
	use GatewayAwareTrait;

	/**
	 * @param mixed $request
	 */
	public function execute($request): void
	{
		RequestNotSupportedException::assertSupports($this, $request);

		$model = ArrayObject::ensureArrayObject($request->getModel());
		ArrayObject::ensureArrayObject($model);

		$gpWebPayActionAction = $this->getGPWebpayAction($request->getToken(), $model);
		$this->gateway->execute($gpWebPayActionAction);
	}

	/**
	 * @param mixed $request
	 */
	public function supports($request): bool
	{
		return
			$request instanceof Capture &&
			$request->getModel() instanceof \ArrayAccess;
	}

	private function getGPWebpayAction(TokenInterface $token, ArrayObject $model): SetGPWebpay
	{
		$gpWebPayActionAction = new SetGPWebpay($token);
		$gpWebPayActionAction->setModel($model);

		return $gpWebPayActionAction;
	}
}
