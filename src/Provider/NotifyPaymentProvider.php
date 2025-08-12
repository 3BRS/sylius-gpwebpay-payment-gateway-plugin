<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Provider;

use Doctrine\ORM\EntityRepository;
use Sylius\Bundle\PaymentBundle\Attribute\AsNotifyPaymentProvider;
use Sylius\Bundle\PaymentBundle\Provider\NotifyPaymentProviderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface;
use Symfony\Component\HttpFoundation\Request;

#[AsNotifyPaymentProvider(priority: 0)]
final readonly class NotifyPaymentProvider implements NotifyPaymentProviderInterface
{
    public function __construct(
        private EntityRepository $entityRepository,
    ) {
    }

    public function supports(Request $request, PaymentMethodInterface $paymentMethod): bool
    {
        // Check if this is a GPWebPay payment method and has the required parameters
        return $paymentMethod->getGatewayConfig()?->getFactoryName() === 'gpwebpay' &&
            $request->query->has('OPERATION') &&
            $request->query->has('ORDERNUMBER');
    }

    public function getPayment(Request $request, PaymentMethodInterface $paymentMethod): PaymentInterface
    {
        $orderNumber = $request->query->get('ORDERNUMBER');

        if (!$orderNumber) {
            throw new \InvalidArgumentException('ORDERNUMBER parameter is required');
        }

        // Find payment by order number and payment method
        $payment = $this->entityRepository->createQueryBuilder('p')
            ->innerJoin('p.order', 'o')
            ->where('p.method = :method')
            ->andWhere('o.number = :orderNumber')
            ->setParameter('method', $paymentMethod)
            ->setParameter('orderNumber', $orderNumber)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if ($payment) {
            assert($payment instanceof PaymentInterface);

            return $payment;
        }

        throw new \RuntimeException(sprintf('Payment with order number "%s" not found', $orderNumber));
    }
}
