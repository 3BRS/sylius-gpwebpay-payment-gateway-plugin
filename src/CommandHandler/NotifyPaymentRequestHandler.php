<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\CommandHandler;

use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Api\GPWebpayApiInterface;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Command\NotifyPaymentRequest;

#[AsMessageHandler]
final readonly class NotifyPaymentRequestHandler
{
    public function __construct(
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private StateMachineInterface $stateMachine,
        private GPWebpayApiInterface $gpWebPayApi,
    ) {
    }

    /**
     * Handles @see PaymentRequestInterface::ACTION_NOTIFY
     *
     * Process GPWebPay callback/webhook notifications
     */
    public function __invoke(NotifyPaymentRequest $notifyPaymentRequest): void
    {
        $paymentRequest = $this->paymentRequestProvider->provide($notifyPaymentRequest);
        $responseData = $notifyPaymentRequest->getResponseData();

        // Store the response data for later processing
        $paymentRequest->setResponseData($responseData);

        // Verify the GPWebPay response signature and process the result
        $isValid = $this->gpWebPayApi->verifyResponse($responseData, $paymentRequest->getMethod()?->getGatewayConfig()?->getConfig() ?? []);

        if (!$isValid) {
            // Mark as failed if signature verification fails
            if ($this->stateMachine->can(
                $paymentRequest,
                PaymentRequestTransitions::GRAPH,
                PaymentRequestTransitions::TRANSITION_FAIL,
            )) {
                $this->stateMachine->apply(
                    $paymentRequest,
                    PaymentRequestTransitions::GRAPH,
                    PaymentRequestTransitions::TRANSITION_FAIL,
                );
            }

            return;
        }

        // Process successful payment
        if (isset($responseData['PRCODE']) && $responseData['PRCODE'] === '0') {
            if ($this->stateMachine->can(
                $paymentRequest,
                PaymentRequestTransitions::GRAPH,
                PaymentRequestTransitions::TRANSITION_COMPLETE,
            )) {
                $this->stateMachine->apply(
                    $paymentRequest,
                    PaymentRequestTransitions::GRAPH,
                    PaymentRequestTransitions::TRANSITION_COMPLETE,
                );
            }

            return;
        }

        // Mark as failed for non-successful PRCODE
        if ($this->stateMachine->can(
            $paymentRequest,
            PaymentRequestTransitions::GRAPH,
            PaymentRequestTransitions::TRANSITION_FAIL,
        )) {
            $this->stateMachine->apply(
                $paymentRequest,
                PaymentRequestTransitions::GRAPH,
                PaymentRequestTransitions::TRANSITION_FAIL,
            );
        }
    }
}
