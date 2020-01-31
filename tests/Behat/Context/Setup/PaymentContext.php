<?php

declare(strict_types=1);

namespace Tests\MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Repository\PaymentMethodRepositoryInterface;

final class PaymentContext implements Context
{
	/** @var SharedStorageInterface */
	private $sharedStorage;

	/** @var PaymentMethodRepositoryInterface */
	private $paymentMethodRepository;

	/** @var ExampleFactoryInterface */
	private $paymentMethodExampleFactory;

	/** @var ObjectManager */
	private $paymentMethodManager;

	/** @var array */
	private $gatewayFactories;

	public function __construct(
		SharedStorageInterface $sharedStorage,
		PaymentMethodRepositoryInterface $paymentMethodRepository,
		ExampleFactoryInterface $paymentMethodExampleFactory,
		ObjectManager $paymentMethodManager,
		array $gatewayFactories
	) {
		$this->sharedStorage = $sharedStorage;
		$this->paymentMethodRepository = $paymentMethodRepository;
		$this->paymentMethodExampleFactory = $paymentMethodExampleFactory;
		$this->paymentMethodManager = $paymentMethodManager;
		$this->gatewayFactories = $gatewayFactories;
	}

	/**
	 * @Given the store allows paying with name :paymentMethodName and code :paymentMethodCode gpwebpay gateway
	 */
	public function theStoreHasPaymentMethodWithCodeAndGPWebpayCheckoutGateway(
		$paymentMethodName,
		$paymentMethodCode
	) {
		$paymentMethod = $this->createPaymentMethod($paymentMethodName, $paymentMethodCode, 'GP webpay');
		$paymentMethod->getGatewayConfig()->setConfig([
			'merchantNumber' => 'TEST',
			'keyPrivateName' => 'TEST',
			'keyPrivatePassword' => 'TEST',
			'sandbox' => true,
		]);

		$this->paymentMethodManager->flush();
	}

	/**
	 * @param string $name
	 * @param string $code
	 * @param string $gatewayFactory
	 * @param string $description
	 * @param bool $addForCurrentChannel
	 * @param int|null $position
	 *
	 * @return PaymentMethodInterface
	 */
	private function createPaymentMethod(
		$name,
		$code,
		$gatewayFactory = 'Offline',
		$description = '',
		$addForCurrentChannel = true,
		$position = null
	) {
		$gatewayFactory = array_search($gatewayFactory, $this->gatewayFactories);

		/** @var PaymentMethodInterface $paymentMethod */
		$paymentMethod = $this->paymentMethodExampleFactory->create([
			'name' => ucfirst($name),
			'code' => $code,
			'description' => $description,
			'gatewayName' => $gatewayFactory,
			'gatewayFactory' => $gatewayFactory,
			'enabled' => true,
			'channels' => ($addForCurrentChannel && $this->sharedStorage->has('channel')) ? [$this->sharedStorage->get('channel')] : [],
		]);

		if (null !== $position) {
			$paymentMethod->setPosition((int) $position);
		}

		$this->sharedStorage->set('payment_method', $paymentMethod);
		$this->paymentMethodRepository->add($paymentMethod);

		return $paymentMethod;
	}
}
