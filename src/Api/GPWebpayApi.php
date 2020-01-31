<?php

declare(strict_types=1);

namespace MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Api;

use AdamStipak\Webpay\Api;
use AdamStipak\Webpay\PaymentRequest;
use AdamStipak\Webpay\PaymentResponse;
use AdamStipak\Webpay\PaymentResponseException;
use AdamStipak\Webpay\Signer;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Sylius\Component\Core\Context\ShopperContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\TranslatorInterface;

class GPWebpayApi implements GPWebpayApiInterface
{
	/** @var ShopperContextInterface */
	protected $shopperContext;

	/** @var TranslatorInterface */
	protected $translator;

	/** @var KernelInterface */
	protected $kernel;
	/**
	 * @var LoggerInterface
	 */
	protected $logger;
	/**
	 * @var RequestStack
	 */
	protected $requestStack;

	public function __construct(
		KernelInterface $kernel,
		TranslatorInterface $translator,
		ShopperContextInterface $shopperContext,
		LoggerInterface $logger,
		RequestStack $requestStack
	) {
		$this->kernel = $kernel;
		$this->translator = $translator;
		$this->shopperContext = $shopperContext;
		$this->logger = $logger;
		$this->requestStack = $requestStack;
	}

	private function createApi(bool $sandbox, string $keyName, string $keyPassword, string $merchantNumber): Api
	{
		$kernelDir = $this->kernel->getRootDir();

		$clientCert = $sandbox
			? $kernelDir . '/../config/gpWebPayKeys/clientKeys/sandbox/' . $keyName
			: $kernelDir . '/../config/gpWebPayKeys/clientKeys/prod/' . $keyName;

		$serverCert = $sandbox
			? __DIR__ . '/../Resources/keys/serverKeys/sandbox/gpe.signing_test.pem'
			: __DIR__ . '/../Resources/keys/serverKeys/prod/gpe.signing_prod.pem';

		$apiEndpoint = $sandbox
			? 'https://test.3dsecure.gpwebpay.com/pgw/order.do'
			: 'https://3dsecure.gpwebpay.com/pgw/order.do';

		$signer = new Signer($clientCert, $keyPassword, $serverCert);

		return new Api($merchantNumber, $apiEndpoint, $signer);
	}

	private function getCurrency(string $currency): string
	{
		$reflectionClass = new ReflectionClass(PaymentRequest::class);

		return (string) $reflectionClass->getConstants()[$currency];
	}

	public function create(array $order, string $merchantNumber, bool $sandbox, string $keyName, string $keyPassword): array
	{
		$api = $this->createAPI($sandbox, $keyName, $keyPassword, $merchantNumber);

		$orderNumber = $order['orderNumber'];
		$amount = $order['amount'] / 100;
		$currency = $this->getCurrency($order['currency']);
		$depositFlag = 1;
		$url = $order['returnUrl'];
		$merOrderNumber = null;

		$request = new PaymentRequest($orderNumber, $amount, $currency, $depositFlag, $url, $merOrderNumber);

		return [
			'orderId' => $order['orderNumber'],
			'gatewayLocationUrl' => $api->createPaymentRequestUrl($request),
		];
	}

	public function retrieve(string $merchantNumber, bool $sandbox, string $keyName, string $keyPassword): string
	{
		$request = $this->requestStack->getMasterRequest();
		assert($request !== null);

		$operation = $request->get('OPERATION');
		$ordernumber = $request->get('ORDERNUMBER');
		$merordernum = $request->get('MERORDERNUM');
		$prcode = $request->get('PRCODE');
		$srcode = $request->get('SRCODE');
		$resulttext = $request->get('RESULTTEXT');
		$digest = $request->get('DIGEST');
		$digest1 = $request->get('DIGEST1');

		$response = new PaymentResponse($operation, $ordernumber, $merordernum, $prcode, $srcode, $resulttext, $digest, $digest1);

		try {
			$api = $this->createAPI($sandbox, $keyName, $keyPassword, $merchantNumber);
			$api->verifyPaymentResponse($response);
		} catch (PaymentResponseException $e) {
			$this->logger->error($e->getMessage());

			return GPWebpayApiInterface::CANCELED;
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());

			return GPWebpayApiInterface::CANCELED;
		}

		if ($response->hasError()) {
			return GPWebpayApiInterface::CANCELED;
		}

		return GPWebpayApiInterface::PAID;
	}
}
