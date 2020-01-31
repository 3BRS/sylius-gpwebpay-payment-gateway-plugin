<?php

declare(strict_types=1);

namespace Tests\MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Behat\Pages\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\Channel\UpdatePageInterface as BaseUpdatePageInterface;

interface EditPageInterface extends BaseUpdatePageInterface
{
	public function setGPWebpayMerchantNumber(string $value): void;

	public function setGPWebpayKeyPassword(string $value): void;

	public function setGPWebpayKey(string $value): void;
}
