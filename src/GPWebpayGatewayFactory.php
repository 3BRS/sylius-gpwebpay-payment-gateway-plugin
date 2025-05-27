<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Action\CaptureAction;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Action\ConvertPaymentAction;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Action\StatusAction;

/** @noinspection PhpUnused used in services.yml */
class GPWebpayGatewayFactory extends GatewayFactory
{
    /**
     * @inheritdoc
     */
    protected function populateConfig(ArrayObject $config): void
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
                'keyPrivate' => '',
                'keyPrivatePassword' => '',
                'sandbox' => true,
            ];
            assert(is_iterable($config['payum.default_options']));
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['merchantNumber', 'keyPrivate', 'keyPrivatePassword'];

            $config['payum.api'] = function (ArrayObject $config) {
                assert(is_array($config['payum.required_options']));
                $config->validateNotEmpty($config['payum.required_options']);

                $gpWebPayConfig = [
                    'allowedPaymentMethods' => $config['allowedPaymentMethods'],
                    'preferredPaymentMethod' => $config['preferredPaymentMethod'],
                    'merchantNumber' => $config['merchantNumber'],
                    'keyPrivate' => $config['keyPrivate'],
                    'keyPrivatePassword' => $config['keyPrivatePassword'],
                    'sandbox' => $config['sandbox'],
                ];

                return $gpWebPayConfig;
            };
        }
    }
}
