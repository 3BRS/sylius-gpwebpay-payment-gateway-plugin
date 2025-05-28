<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\Persistence\ObjectManager;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Repository\PaymentMethodRepositoryInterface;

final class PaymentContext implements Context
{
    public function __construct(
        private readonly SharedStorageInterface $sharedStorage,
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
        private readonly ExampleFactoryInterface $paymentMethodExampleFactory,
        private readonly ObjectManager $paymentMethodManager,
        private readonly array $gatewayFactories,
    ) {
    }

    /**
     * @Given the store allows paying with name :paymentMethodName and code :paymentMethodCode gpwebpay gateway
     */
    public function theStoreHasPaymentMethodWithCodeAndGPWebpayCheckoutGateway(
        string $paymentMethodName,
        string $paymentMethodCode,
    ): void {
        $paymentMethod = $this->createPaymentMethod($paymentMethodName, $paymentMethodCode, 'GP webpay');
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        assert($gatewayConfig !== null, 'Gateway config should not be null');
        $gatewayConfig->setConfig([
            'merchantNumber' => 'TEST',
            'keyPrivateName' => 'TEST',
            'keyPrivatePassword' => 'TEST',
            'sandbox' => true,
        ]);

        $this->paymentMethodManager->flush();
    }

    private function createPaymentMethod(
        string $name,
        string $code,
        string $gatewayFactory = 'Offline',
        string $description = '',
        bool $addForCurrentChannel = true,
        ?int $position = null,
    ): PaymentMethodInterface {
        $gatewayFactory = array_search($gatewayFactory, $this->gatewayFactories);

        $paymentMethod = $this->paymentMethodExampleFactory->create([
            'name' => ucfirst($name),
            'code' => $code,
            'description' => $description,
            'gatewayName' => $gatewayFactory,
            'gatewayFactory' => $gatewayFactory,
            'enabled' => true,
            'channels' => ($addForCurrentChannel && $this->sharedStorage->has('channel'))
                ? [$this->sharedStorage->get('channel')]
                : [],
        ]);

        assert($paymentMethod instanceof PaymentMethodInterface);

        if (null !== $position) {
            $paymentMethod->setPosition((int) $position);
        }

        $this->sharedStorage->set('payment_method', $paymentMethod);
        $this->paymentMethodRepository->add($paymentMethod);

        return $paymentMethod;
    }
}
