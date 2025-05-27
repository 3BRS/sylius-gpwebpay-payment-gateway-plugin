<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Api;

use Payum\ISO4217\ISO4217;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Context\ShopperContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\WebpaySdk\Api;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\WebpaySdk\PaymentRequest;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\WebpaySdk\PaymentResponse;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\WebpaySdk\PaymentResponseException;
use ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Model\WebpaySdk\Signer;

class GPWebpayApi implements GPWebpayApiInterface
{
    /** @var ShopperContextInterface */
    protected $shopperContext;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var LoggerInterface */
    protected $logger;

    /** @var RequestStack */
    protected $requestStack;

    public function __construct(
        TranslatorInterface $translator,
        ShopperContextInterface $shopperContext,
        LoggerInterface $logger,
        RequestStack $requestStack,
    ) {
        $this->translator = $translator;
        $this->shopperContext = $shopperContext;
        $this->logger = $logger;
        $this->requestStack = $requestStack;
    }

    protected function createApi(bool $sandbox, string $clientPrivateKey, string $keyPassword, string $merchantNumber): Api
    {
        $serverCert = $sandbox
            ? __DIR__ . '/../Resources/keys/serverKeys/sandbox/gpe.signing_test.pem'
            : __DIR__ . '/../Resources/keys/serverKeys/prod/gpe.signing_prod.pem';

        $apiEndpoint = $sandbox
            ? 'https://test.3dsecure.gpwebpay.com/pgw/order.do'
            : 'https://3dsecure.gpwebpay.com/pgw/order.do';

        $signer = new Signer($clientPrivateKey, $keyPassword, $serverCert);

        return new Api($merchantNumber, $apiEndpoint, $signer);
    }

    protected function getCurrency(string $currencyCode): int
    {
        $iso4217 = new ISO4217();
        $currency = $iso4217->findByAlpha3($currencyCode);

        return (int) $currency->getNumeric();
    }

    public function create(array $order, string $merchantNumber, bool $sandbox, string $keyName, string $keyPassword, ?string $preferredPaymentMethod, ?array $allowedPaymentMethods): array
    {
        $api = $this->createAPI($sandbox, $keyName, $keyPassword, $merchantNumber);

        $orderNumber = (int) $order['orderNumber'];
        $amount = $order['amount'] / 100;
        $currency = $this->getCurrency($order['currency']);
        $depositFlag = 1;
        $url = $order['returnUrl'];
        $merOrderNumber = null;

        $request = new PaymentRequest($orderNumber, $amount, $currency, $depositFlag, $url, $merOrderNumber);
        if ($preferredPaymentMethod !== null && $preferredPaymentMethod !== '') {
            $request->setPreferredPaymentMethod($preferredPaymentMethod);
        }
        if ($allowedPaymentMethods !== null && count($allowedPaymentMethods) > 0) {
            $request->setAllowedPaymentMethods(implode(',', $allowedPaymentMethods));
        }

        return [
            'orderId' => $order['orderNumber'],
            'gatewayLocationUrl' => $api->createPaymentRequestUrl($request),
        ];
    }

    public function retrieve(string $merchantNumber, bool $sandbox, string $clientPrivateKey, string $keyPassword): string
    {
        $request = $this->requestStack->getMainRequest();
        assert($request !== null);

        $operation = (string) $request->get('OPERATION');
        $ordernumber = (string) $request->get('ORDERNUMBER');
        $merordernum = $request->get('MERORDERNUM');
        $merordernum = $merordernum !== null
            ? (string) $merordernum
            : null;
        $prcode = (int) $request->get('PRCODE');
        $srcode = (int) $request->get('SRCODE');
        $resulttext = (string) $request->get('RESULTTEXT');
        $digest = (string) $request->get('DIGEST');
        $digest1 = (string) $request->get('DIGEST1');

        $response = new PaymentResponse($operation, $ordernumber, $merordernum, $prcode, $srcode, $resulttext, $digest, $digest1);

        try {
            $api = $this->createAPI($sandbox, $clientPrivateKey, $keyPassword, $merchantNumber);
            $api->verifyPaymentResponse($response);
        } catch (PaymentResponseException|\Exception $e) {
            $this->logger->error($e->getMessage());

            return GPWebpayApiInterface::CANCELED;
        }

        if ($response->hasError()) {
            return GPWebpayApiInterface::CANCELED;
        }

        return GPWebpayApiInterface::PAID;
    }
}
