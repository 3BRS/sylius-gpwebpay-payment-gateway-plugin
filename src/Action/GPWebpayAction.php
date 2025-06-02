<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Security\TokenAggregateInterface;
use Payum\Core\Security\TokenInterface;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Api\GPWebpayApiInterface;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\SetGPWebpay;

class GPWebpayAction implements ApiAwareInterface, ActionInterface
{
    protected GPWebpayApiInterface $gpWebPayApi;

    private array $api = [];

    public function __construct(GPWebpayApiInterface $gpWebPayApi)
    {
        $this->gpWebPayApi = $gpWebPayApi;
    }

    /** @param mixed $api */
    public function setApi($api): void
    {
        if (!is_array($api)) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * @param mixed $request
     */
    public function execute($request): void
    {
        assert($request instanceof ModelAggregateInterface);

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
        assert($request instanceof TokenAggregateInterface);
        $token = $request->getToken();
        assert($token !== null);
        $order = $this->prepareOrder($token, $model);
        $response = $this->gpWebPayApi->create($order, $merchantNumber, $sandbox, $clientPrivateKey, $keyPassword, $preferredPaymentMethod, $allowedPaymentMethods);

        if ($response) {
            $model['orderId'] = $response['orderId'];
            assert($request instanceof ModelAwareInterface);
            $request->setModel($model);

            throw new HttpPostRedirect($response['gatewayLocationUrl'], $response['gatewayPostData']);
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

    private function prepareOrder(
        TokenInterface $token,
        ArrayObject $model,
    ): array {
        $order = [];
        $order['currency'] = $model['currencyCode'];
        $order['amount'] = $model['totalAmount'];
        $order['orderNumber'] = $model['number'];
        $order['psd2'] = $model['psd2'];
        $order['returnUrl'] = $token->getTargetUrl();

        return $order;
    }
}
