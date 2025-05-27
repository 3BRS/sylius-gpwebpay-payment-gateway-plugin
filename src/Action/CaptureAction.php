<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenAggregateInterface;
use Payum\Core\Security\TokenInterface;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\SetGPWebpay;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        assert($request instanceof ModelAggregateInterface);
        $model = ArrayObject::ensureArrayObject($request->getModel());
        ArrayObject::ensureArrayObject($model);

        assert($request instanceof TokenAggregateInterface);
        $token = $request->getToken();
        if (!$token instanceof TokenInterface) {
            throw new \LogicException('Token must be set.');
        }
        $gpWebPayActionAction = $this->getGPWebpayAction($token, $model);
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
