<?php

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\ResponseProvider\Partials;

use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Form\Type\GPWebpayGatewayConfigurationType;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\ResponseProvider\Exception\InvalidPaymentGatewayConfiguration;

trait GpWebPayApiConfigurationTrait
{
    private function getMerchantNumber(array $gpWebPayConfig): string
    {
        $merchantNumber = (string)$this->getValueFromGatewayConfiguration(
            GPWebpayGatewayConfigurationType::MERCHANT_NUMBER,
            $gpWebPayConfig,
        );
        if ($merchantNumber === '') {
            throw new InvalidPaymentGatewayConfiguration(
                'GpWebPay merchant number is missing in the configuration',
            );
        }

        return $merchantNumber;
    }

    private function isSandbox(array $gpWebPayConfig): bool
    {
        return (bool)$this->getValueFromGatewayConfiguration(
            GPWebpayGatewayConfigurationType::SANDBOX,
            $gpWebPayConfig,
        );
    }

    private function getClientPrivateKey(array $gpWebPayConfig): string
    {
        $clientPrivateKey = (string)$this->getValueFromGatewayConfiguration(
            GPWebpayGatewayConfigurationType::KEY_PRIVATE,
            $gpWebPayConfig,
        );
        if ($clientPrivateKey === '') {
            throw new InvalidPaymentGatewayConfiguration(
                'GpWebPay client private key is missing in the configuration',
            );
        }

        return $clientPrivateKey;
    }

    private function getClientPrivateKeyPassword(array $gpWebPayConfig): string
    {
        $clientPrivateKeyPassword = (string)$this->getValueFromGatewayConfiguration(
            GPWebpayGatewayConfigurationType::KEY_PRIVATE_PASSWORD,
            $gpWebPayConfig,
        );
        if ($clientPrivateKeyPassword === '') {
            throw new InvalidPaymentGatewayConfiguration(
                'GpWebPay client private key password is missing in the configuration',
            );
        }

        return $clientPrivateKeyPassword;
    }

    private function getValueFromGatewayConfiguration(
        string $key,
        array $gpWebPayConfig,
    ): string|int|float|array|bool|null
    {
        if (!array_key_exists($key, $gpWebPayConfig)) {
            throw new InvalidPaymentGatewayConfiguration(
                sprintf('GpWebPay configuration key "%s" is missing', $key),
            );
        }

        return $gpWebPayConfig[$key];
    }
}
