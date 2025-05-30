<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\ResponseProvider;

use Sylius\Bundle\PaymentBundle\Provider\HttpResponseProviderInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Api\GPWebpayApiInterface;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Form\Type\GPWebpayGatewayConfigurationType;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\Exception\InvalidPayloadException;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\OrderForPayment;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\ResponseProvider\Exception\InvalidPaymentGatewayConfiguration;

final readonly class CaptureHttpResponseProvider implements HttpResponseProviderInterface
{
    public function __construct(
        private GPWebpayApiInterface $gpWebPayApi,
        private RouterInterface $router,
    ) {
    }

    public function supports(
        RequestConfiguration $requestConfiguration,
        PaymentRequestInterface $paymentRequest,
    ): bool {
        return $paymentRequest->getAction() === PaymentRequestInterface::ACTION_CAPTURE;
    }

    public function getResponse(
        RequestConfiguration $requestConfiguration,
        PaymentRequestInterface $paymentRequest,
    ): Response {
        $payloadArray = $paymentRequest->getPayload();
        if (!is_array($payloadArray)) {
            throw new InvalidPayloadException('Payment request payload expected to be an array');
        }
        $orderForPayment = OrderForPayment::fromArray($payloadArray);

        $gpWebPayConfig = $paymentRequest->getPayment()->getMethod()?->getGatewayConfig()?->getConfig();
        if ($gpWebPayConfig === null) {
            throw new InvalidPaymentGatewayConfiguration(
                'GpWebPay payment method configuration is missing',
            );
        }

        $requestData = $this->gpWebPayApi->create(
            [
                'orderNumber' => (int) $orderForPayment->getOrderNumber(),
                'amount' => $orderForPayment->getAmount(),
                'currency' => $orderForPayment->getCurrency(),
                'returnUrl' => $this->router->generate(
                    'sylius_shop_order_after_pay',
                    ['hash' => $paymentRequest->getHash()],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
            ],
            $this->getMerchantNumber($gpWebPayConfig),
            $this->isSandbox($gpWebPayConfig),
            $this->getClientPrivateKey($gpWebPayConfig),
            $this->getClientPrivateKeyPassword($gpWebPayConfig),
            $this->getPreferredPaymentMethod($gpWebPayConfig),
            $this->getAllowedPaymentMethods($gpWebPayConfig),
        );

        return new RedirectResponse($requestData['gatewayLocationUrl']);
        // TODO use sylius_shop_order_after_pay route if already paid
    }

    private function getAllowedPaymentMethods(array $gpWebPayConfig): ?array
    {
        return (array) $this->getValueFromGatewayConfiguration(
            GPWebpayGatewayConfigurationType::ALLOWED_PAYMENT_METHODS,
            $gpWebPayConfig,
        );
    }

    private function getPreferredPaymentMethod(array $gpWebPayConfig): ?string
    {
        $preferredPaymentMethod = (string) $this->getValueFromGatewayConfiguration(
            GPWebpayGatewayConfigurationType::PREFERRED_PAYMENT_METHOD,
            $gpWebPayConfig,
        );
        if ($preferredPaymentMethod === '') {
            return null;
        }

        return $preferredPaymentMethod;
    }

    private function getClientPrivateKeyPassword(array $gpWebPayConfig): string
    {
        $clientPrivateKeyPassword = (string) $this->getValueFromGatewayConfiguration(
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

    private function getClientPrivateKey(array $gpWebPayConfig): string
    {
        $clientPrivateKey = (string) $this->getValueFromGatewayConfiguration(
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

    private function isSandbox(array $gpWebPayConfig): bool
    {
        return (bool) $this->getValueFromGatewayConfiguration(
            GPWebpayGatewayConfigurationType::SANDBOX,
            $gpWebPayConfig,
        );
    }

    private function getMerchantNumber(array $gpWebPayConfig): string
    {
        $merchantNumber = (string) $this->getValueFromGatewayConfiguration(
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

    private function getValueFromGatewayConfiguration(
        string $key,
        array $gpWebPayConfig,
    ): string | int | float | array | bool | null {
        if (!array_key_exists($key, $gpWebPayConfig)) {
            throw new InvalidPaymentGatewayConfiguration(
                sprintf('GpWebPay configuration key "%s" is missing', $key),
            );
        }

        return $gpWebPayConfig[$key];
    }
}
