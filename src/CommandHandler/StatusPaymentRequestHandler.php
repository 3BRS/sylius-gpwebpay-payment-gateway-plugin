<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\CommandHandler;

use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Command\StatusPaymentRequest;

#[AsMessageHandler]
final readonly class StatusPaymentRequestHandler
{
    public function __construct(
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private StateMachineInterface $stateMachine,
    ) {
    }

    /**
     * @see PaymentRequestInterface::ACTION_STATUS
     */
    public function __invoke(StatusPaymentRequest $statusPaymentRequest): void
    {
        $paymentRequest = $this->paymentRequestProvider->provide($statusPaymentRequest);

        // TODO \ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Action\StatusAction::execute
        // Custom capture logic for the payment provider would go here.
        // Example: communicating with the payment gateway API to capture funds.

        // TODO
        // Mark the PaymentRequest as complete|process|fail|cancel.
        $this->stateMachine->apply(
            $paymentRequest,
            PaymentRequestTransitions::GRAPH,
            PaymentRequestTransitions::TRANSITION_COMPLETE,
        );
    }
}
