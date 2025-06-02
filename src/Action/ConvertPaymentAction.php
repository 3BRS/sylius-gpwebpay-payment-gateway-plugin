<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Api\GPWebpayApiInterface;

class ConvertPaymentAction implements ActionInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    /**
     * @param mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        assert($request instanceof Convert);
        $payment = $request->getSource();
        assert($payment instanceof PaymentInterface);

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details['totalAmount'] = $payment->getTotalAmount();
        $details['currencyCode'] = $payment->getCurrencyCode();
        $details['extOrderId'] = $payment->getNumber();
        $details['number'] = $payment->getNumber() . date('His');
        $details['status'] = GPWebpayApiInterface::CREATED;

        $order = $this->orderRepository->findOneByNumber($payment->getNumber());
        assert($order instanceof OrderInterface);
        $details['psd2'] = $this->psd2ToArray($order);

        $request->setResult((array) $details);
    }

    /**
     * @return array<string, array<string, array<string, string|null>>>
     */
    protected function psd2ToArray(OrderInterface $order): array
    {
        assert($order->getCustomer() !== null);
        assert($order->getBillingAddress() !== null);

        $details = [];
        $details['cardholderInfo']['cardholderDetails']['name'] = $order->getBillingAddress()->getFullName();
        $details['cardholderInfo']['cardholderDetails']['email'] = $this->manageEmail($order->getCustomer()->getEmail());

        return $details;
    }

    private function manageEmail(?string $email): string
    {
        if ($email === null) {
            return '';
        }
        $separator = '+';
        if (str_contains($email, $separator)) {
            $emailArray = explode($separator, $email);
            $lastArray = explode('@', $emailArray[count($emailArray) - 1]);

            $email = $emailArray[0] . '@' . $lastArray[count($lastArray) - 1];
        }

        $email = preg_replace('/[^a-zA-Z0-9@.]+/', '', $email);
        assert(is_string($email));

        return $email;
    }

    /**
     * @param mixed $request
     */
    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array';
    }
}
