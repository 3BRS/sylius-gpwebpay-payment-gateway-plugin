<h1 align="center">
    GP webpay Payment Gateway Plugin
</h1>

## Features

* Card payments as supported by [GP webpay](https://www.gpwebpay.cz/en/home/)
* Fully integrated as [Sylius](https://sylius.com/) payment method
* Using more different gateways at once or per channel

<p align="center">
	<img src="https://raw.githubusercontent.com/3BRS/sylius-gpwebpay-payment-gateway-plugin/main/doc/admin-11.png"/>
</p>
<p align="center">
	<img src="https://raw.githubusercontent.com/3BRS/sylius-gpwebpay-payment-gateway-plugin/main/doc/admin-2.png"/>
</p>

## Installation

1. Run `$ composer require
   3brs/sylius-gpwebpay-payment-gateway-plugin`.
1. Add plugin classes to your `config/bundles.php`:

   ```php
   return [
      ...
      ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\ThreeBRSSyliusGPWebpayPaymentGatewayPlugin::class => ['all' => true],
   ];
   ```
1. Load plugin configuration by `config/packages/threebrs_sylius_gpwebpay_payment_gateway_plugin.yaml`:

   ```yaml
   imports:
    - { resource: "@ThreeBRSSyliusGPWebpayPaymentGatewayPlugin/Resources/config/config.yaml" }
   ```

1. generate keys to keep gateway credentials safe:

   ```bash
   bin/console sylius:payment:generate-key
   ```
  
## Usage

* <b>Create GP webpay payment type</b><br>in Sylius admin panel, _Configuration -> Payment methods_<br>

## Sylius 2 pay workflow

- Customer hit _Pay_ button
- Request goes to `\Sylius\Bundle\CoreBundle\OrderPay\Controller\OrderPayController::payAction`
- That will emit redirect 302 by `\Sylius\Bundle\CoreBundle\OrderPay\Provider\PaymentRequestPayResponseProvider::getResponse` to route like `/en_US/payment-request/pay/0197204a-8284-7301-9b30-e151c7d14ec5`
- That request goes to `\Sylius\Bundle\CoreBundle\OrderPay\Action\PaymentRequestPayAction::__invoke`
  - that will dispatch command to process the payment in `\Sylius\Bundle\PaymentBundle\Processor\HttpResponseProcessor::process`
  - that will find command provider fitting the payment method in `\Sylius\Bundle\PaymentBundle\CommandProvider\AbstractServiceCommandProvider::provide`, in our case `\ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\CommandProvider\CapturePaymentRequestCommandProvider`
  - the provider will give command `\ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Command\CapturePaymentRequest`
  - that will be handled by `\ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\CommandHandler\CapturePaymentRequestHandler`
    - ⚠️ if messenger is configured as async, the payment will not be processed immediately and customer will end on Pay page again ⚠️
    - that command handler may resolve the payment, but in our case it will just prepare payload for the payment gateway webpage
  - then the "capture" response is processed by `\ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\ResponseProvider\CaptureHttpResponseProvider` which in our case will return a redirect to the payment gateway webpage

## Development

### Usage

- Develop your plugin in `/src`
- See `bin/` for useful commands

### Testing

After your changes you must ensure that the tests are still passing.

```bash
docker compose run -u application app composer install
docker compose run -u application app bin/console doctrine:database:create --env=test
docker compose run -u application app bin/console doctrine:schema:update --complete --force --env=test
docker compose run -u node frontend yarn --cwd tests/Application install
docker compose run -u node frontend yarn --cwd tests/Application build

docker compose run -u application -e XDEBUG_MODE=off app bin/behat
docker compose run -u application app bin/phpstan.sh
docker compose run -u application app bin/ecs.sh
```

### Opening Sylius with your plugin

1. Install symfony CLI command: https://symfony.com/download
   - hint: for Docker (with Ubuntu) use _Debian/Ubuntu — APT based
     Linux_ installation steps as `root` user and without `sudo` command
      - you may need to install `curl` first ```apt-get update && apt-get install curl --yes```
2. Run app
   sylius-g-p-webpay-payment-gateway-plugin
```bash
docker compose run -u application app bash
(cd tests/Application && APP_ENV=dev bin/console doctrine:database:create)
(cd tests/Application && APP_ENV=dev bin/console doctrine:schema:update --complete --force)
(cd tests/Application && APP_ENV=dev bin/console sylius:fixtures:load)
curl -sS https://get.symfony.com/cli/installer | bash
export PATH="$HOME/.symfony5/bin:$PATH"
(cd tests/Application && APP_ENV=dev symfony server:start --dir=public --port=8081)
```
open `http://127.0.0.1:8081/admin/login`, use `sylius`, `sylius` to login

- change `APP_ENV` to `test` if you need it

License
-------
This library is under the MIT license.

Credits
-------
Developed by [3BRS](https://3brs.com)<br>
Forked from [manGoweb](https://github.com/mangoweb-sylius/SyliusPaymentRestrictionsPlugin).
