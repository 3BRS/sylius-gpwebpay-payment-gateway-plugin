<?php

declare(strict_types=1);

namespace MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Action;

use MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Api\GPWebpayApiInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

class ConvertPaymentAction implements ActionInterface
{
	use GatewayAwareTrait;

	/**
	 * @param mixed $request
	 */
	public function execute($request): void
	{
		RequestNotSupportedException::assertSupports($this, $request);

		/** @var PaymentInterface $payment */
		$payment = $request->getSource();

		$details = ArrayObject::ensureArrayObject($payment->getDetails());

		$details['totalAmount'] = $payment->getTotalAmount();
		$details['currencyCode'] = $payment->getCurrencyCode();
		$details['extOrderId'] = $payment->getNumber();
		$details['number'] = $payment->getNumber() . date('His');
		$details['status'] = GPWebpayApiInterface::CREATED;

		$request->setResult((array) $details);
	}

	/**
	 * @param mixed $request
	 */
	public function supports($request): bool
	{
		return
			$request instanceof Convert &&
			$request->getSource() instanceof PaymentInterface &&
			$request->getTo() === 'array';
	}
}
