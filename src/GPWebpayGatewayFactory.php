<?php

declare(strict_types=1);

namespace MangoSylius\SyliusGPWebpayPaymentGatewayPlugin;

use MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Action\CaptureAction;
use MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Action\ConvertPaymentAction;
use MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class GPWebpayGatewayFactory extends GatewayFactory
{
	/**
	 * {@inheritdoc}
	 */
	protected function populateConfig(ArrayObject $config)
	{
		$config->defaults([
			'payum.factory_name' => 'gpwebpay',
			'payum.factory_title' => 'GPWebpay',

			'payum.action.capture' => new CaptureAction(),
			'payum.action.convert_payment' => new ConvertPaymentAction(),
			'payum.action.status' => new StatusAction(),
		]);

		if (!$config['payum.api']) {
			$config['payum.default_options'] = [
				'allowedPaymentMethods' => '',
				'preferredPaymentMethod' => '',
				'merchantNumber' => '',
				'keyPrivateName' => '',
				'keyPrivatePassword' => '',
				'sandbox' => true,
			];
			$config->defaults($config['payum.default_options']);
			$config['payum.required_options'] = ['merchantNumber', 'keyPrivateName', 'keyPrivatePassword'];

			$config['payum.api'] = function (ArrayObject $config) {
				$config->validateNotEmpty($config['payum.required_options']);

				$gpWebPayConfig = [
					'allowedPaymentMethods' => $config['allowedPaymentMethods'],
					'preferredPaymentMethod' => $config['preferredPaymentMethod'],
					'merchantNumber' => $config['merchantNumber'],
					'keyPrivateName' => $config['keyPrivateName'],
					'keyPrivatePassword' => $config['keyPrivatePassword'],
					'sandbox' => $config['sandbox'],
				];

				return $gpWebPayConfig;
			};
		}
	}
}
