<p align="center">
    <a href="https://www.mangoweb.cz/en/" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/38423357?s=200&v=4"/>
    </a>
</p>
<h1 align="center">
    GP webpay Payment Gateway Plugin
    <br />
        <a href="https://packagist.org/packages/mangoweb-sylius/sylius-gpwebpay-payment-gateway-plugin" title="License" target="_blank">
            <img src="https://img.shields.io/packagist/l/mangoweb-sylius/sylius-gpwebpay-payment-gateway-plugin.svg" />
        </a>
        <a href="https://packagist.org/packages/mangoweb-sylius/sylius-gpwebpay-payment-gateway-plugin" title="Version" target="_blank">
            <img src="https://img.shields.io/packagist/v/mangoweb-sylius/sylius-gpwebpay-payment-gateway-plugin.svg" />
        </a>
        <a href="https://travis-ci.org/mangoweb-sylius/SyliusGPWebpayPaymentGatewayPlugin" title="Build status" target="_blank">
            <img src="https://img.shields.io/travis/mangoweb-sylius/SyliusGPWebpayPaymentGatewayPlugin/master.svg" />
        </a>
</h1>

## Features

* Card payments as supported by GP webpay
* Fully integrated as Sylius payment method
* Using more different gateways at once or per channel

<p align="center">
	<img src="https://raw.githubusercontent.com/mangoweb-sylius/SyliusGPWebpayPaymentGatewayPlugin/master/doc/admin-11.png"/>
</p>
<p align="center">
	<img src="https://raw.githubusercontent.com/mangoweb-sylius/SyliusGPWebpayPaymentGatewayPlugin/master/doc/admin-2.png"/>
</p>

## Installation

1. Run `$ composer require mangoweb-sylius/sylius-gpwebpay-payment-gateway-plugin`.
1. Add plugin classes to your `config/bundles.php`:

   ```php
   return [
      ...
      ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\ThreeBRSSyliusGPWebpayPaymentGatewayPlugin::class => ['all' => true],
   ];
   ```
  
## Usage

* <b>Create GP webpay payment type</b><br>in Sylius admin panel, _Configuration -> Payment methods_<br>

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
