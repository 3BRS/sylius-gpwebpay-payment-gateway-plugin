<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\CommandHandler;

use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentRepository;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Command\CapturePaymentRequest;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\OrderForPayment;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Provider\GpWebPayOrderNumberProviderInterface;

#[AsMessageHandler]
final readonly class CapturePaymentRequestHandler
{
    public function __construct(
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private StateMachineInterface $stateMachine,
        private GpWebPayOrderNumberProviderInterface $gpWebPayOrderNumberProvider,
        private PaymentRepository $paymentRepository,
    ) {
    }

    /**
     * Handles @see PaymentRequestInterface::ACTION_CAPTURE
     *
     * Prepare the payment request for capture, later processed on the payment provider website, @see \ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\ResponseProvider\CaptureHttpResponseProvider::getResponse
     */
    public function __invoke(CapturePaymentRequest $capturePaymentRequest): void
    {
        $paymentRequest = $this->paymentRequestProvider->provide($capturePaymentRequest);

        $oderForPayment = $this->createOderForPayment($paymentRequest);
        $paymentRequest->setPayload($oderForPayment->toArray());
        $this->paymentRepository->add($paymentRequest);

        if ($this->stateMachine->can(
            $paymentRequest,
            PaymentRequestTransitions::GRAPH,
            PaymentRequestTransitions::TRANSITION_PROCESS,
        )) {
            $this->stateMachine->apply(
                $paymentRequest,
                PaymentRequestTransitions::GRAPH,
                PaymentRequestTransitions::TRANSITION_PROCESS,
            );
        }
    }

    private function createOderForPayment(PaymentRequestInterface $paymentRequest): OrderForPayment
    {
        $payment = $paymentRequest->getPayment();
        assert($payment instanceof PaymentInterface);

        return new OrderForPayment(
            currency: $payment->getCurrencyCode(),
            amount: $payment->getAmount(),
            orderNumber: $this->gpWebPayOrderNumberProvider->provideOrderNumber($payment),
        );
    }
}
