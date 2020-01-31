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
	<img src="https://raw.githubusercontent.com/mangoweb-sylius/SyliusGPWebpayPaymentGatewayPlugin/master/doc/admin-1.png"/>
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
      MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\MangoSyliusGPWebpayPaymentGatewayPlugin::class => ['all' => true],
   ];
   ```
  
## Usage

* <b>Create GP webpay payment type</b><br>in Sylius admin panel<br>
* <b>Insert client SANDBOX key</b><br>put the key into the file `/config/gpWebPayKeys/clientKeys/sandbox/{Key file name}`
* <b>Insert client PRODUCTION key</b><br>put the key into the file `/config/gpWebPayKeys/clientKeys/prod/{Key file name}`

Name of the file with the key is not important, just keep it the same for sandbox and production and remember to put the same filename (without its path) into the "Key file name" field. Recpect lowercas and uppercase characters.

## Development

### Usage

- Develop your plugin in `/src`
- See `bin/` for useful commands

### Testing


After your changes you must ensure that the tests are still passing.

```bash
$ composer install
$ bin/console doctrine:schema:create -e test
$ bin/behat
$ bin/phpstan.sh
$ bin/ecs.sh
```

License
-------
This library is under the MIT license.

Credits
-------
Developed by [manGoweb](https://www.mangoweb.eu/).
