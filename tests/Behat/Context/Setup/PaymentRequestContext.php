<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Sylius\Component\Payment\Repository\PaymentRequestRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;

final readonly class PaymentRequestContext implements Context
{
    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private OrderRepositoryInterface $orderRepository,
        private PaymentRequestRepositoryInterface $paymentRequestRepository,
        private KernelBrowser $client,
    ) {
    }

    /**
     * @Given I have placed an order with :paymentMethodName payment method
     */
    public function iHavePlacedAnOrderWithPaymentMethod(string $paymentMethodName): void
    {
        // This assumes an order has already been created in the previous steps
        $order = $this->sharedStorage->get('order');
        assert($order instanceof OrderInterface);

        $this->sharedStorage->set('order', $order);
    }

    /**
     * @Given the payment request is in :state state
     */
    public function thePaymentRequestIsInState(string $state): void
    {
        $order = $this->sharedStorage->get('order');
        assert($order instanceof OrderInterface);

        $payment = $order->getLastPayment();
        assert($payment instanceof PaymentInterface);

        // Find the payment request for this payment
        $paymentRequests = $this->paymentRequestRepository->findBy(['payment' => $payment]);
        if (empty($paymentRequests)) {
            throw new \RuntimeException('No payment request found for the payment');
        }

        $paymentRequest = $paymentRequests[0];
        $paymentRequest->setState($state);

        $this->sharedStorage->set('payment_request', $paymentRequest);
    }

    /**
     * @When GPWebPay sends a successful payment notification
     */
    public function gpwebpaySendsASuccessfulPaymentNotification(): void
    {
        $paymentRequest = $this->sharedStorage->get('payment_request');
        assert($paymentRequest instanceof PaymentRequestInterface);

        // Simulate GPWebPay notification parameters
        $notificationData = [
            'OPERATION' => 'CREATE_ORDER',
            'ORDERNUMBER' => '12345',
            'PRCODE' => '0', // Success code
            'SRCODE' => '0',
            'RESULTTEXT' => 'OK',
            'DIGEST' => 'test_digest',
            'DIGEST1' => 'test_digest1',
        ];

        $url = sprintf('/payment/notify/%s', $paymentRequest->getHash());
        $this->client->request('GET', $url, $notificationData);

        $this->sharedStorage->set('response', $this->client->getResponse());
    }

    /**
     * @When GPWebPay sends a failed payment notification
     */
    public function gpwebpaySendsAFailedPaymentNotification(): void
    {
        $paymentRequest = $this->sharedStorage->get('payment_request');
        assert($paymentRequest instanceof PaymentRequestInterface);

        // Simulate GPWebPay notification parameters for failed payment
        $notificationData = [
            'OPERATION' => 'CREATE_ORDER',
            'ORDERNUMBER' => '12345',
            'PRCODE' => '1', // Error code
            'SRCODE' => '1',
            'RESULTTEXT' => 'FAILED',
            'DIGEST' => 'test_digest',
            'DIGEST1' => 'test_digest1',
        ];

        $url = sprintf('/payment/notify/%s', $paymentRequest->getHash());
        $this->client->request('GET', $url, $notificationData);

        $this->sharedStorage->set('response', $this->client->getResponse());
    }

    /**
     * @Then the payment request should be completed
     */
    public function thePaymentRequestShouldBeCompleted(): void
    {
        $paymentRequest = $this->sharedStorage->get('payment_request');
        assert($paymentRequest instanceof PaymentRequestInterface);

        // Refresh from database
        $this->paymentRequestRepository->findOneBy(['id' => $paymentRequest->getId()]);

        if ($paymentRequest->getState() !== PaymentRequestInterface::STATE_COMPLETED) {
            throw new \RuntimeException(sprintf('Expected payment request to be completed, but it is in state "%s"', $paymentRequest->getState()));
        }
    }

    /**
     * @Then the payment request should be failed
     */
    public function thePaymentRequestShouldBeFailed(): void
    {
        $paymentRequest = $this->sharedStorage->get('payment_request');
        assert($paymentRequest instanceof PaymentRequestInterface);

        // Refresh from database
        $this->paymentRequestRepository->findOneBy(['id' => $paymentRequest->getId()]);

        if ($paymentRequest->getState() !== PaymentRequestInterface::STATE_FAILED) {
            throw new \RuntimeException(sprintf('Expected payment request to be failed, but it is in state "%s"', $paymentRequest->getState()));
        }
    }

    /**
     * @Then the order should be marked as paid
     */
    public function theOrderShouldBeMarkedAsPaid(): void
    {
        $order = $this->sharedStorage->get('order');
        assert($order instanceof OrderInterface);

        // Refresh from database
        $order = $this->orderRepository->findOneBy(['id' => $order->getId()]);

        if ($order->getPaymentState() !== OrderPaymentStates::STATE_PAID) {
            throw new \RuntimeException(sprintf('Expected order to be paid, but payment state is "%s"', $order->getPaymentState()));
        }
    }

    /**
     * @Then the order should remain unpaid
     */
    public function theOrderShouldRemainUnpaid(): void
    {
        $order = $this->sharedStorage->get('order');
        assert($order instanceof OrderInterface);

        // Refresh from database
        $order = $this->orderRepository->findOneBy(['id' => $order->getId()]);

        if ($order->getPaymentState() === OrderPaymentStates::STATE_PAID) {
            throw new \RuntimeException('Expected order to remain unpaid, but it is marked as paid');
        }
    }
}
