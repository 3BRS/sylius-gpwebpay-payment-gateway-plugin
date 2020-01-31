<?php

declare(strict_types=1);

namespace Tests\MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Behat\Pages\Admin\PaymentMethod\EditPageInterface;

final class ManagingPaymentMethodsContext implements Context
{
	/** @var EditPageInterface */
	private $updatePage;

	public function __construct(
		EditPageInterface $updatePage
	) {
		$this->updatePage = $updatePage;
	}

	/**
	 * @When I configure it with test GP webpay credentials
	 */
	public function iConfigureItWithTestGPWebpayCredentials()
	{
		$this->updatePage->setGPWebpayMerchantNumber('TEST');
		$this->updatePage->setGPWebpayKeyPassword('TEST');
		$this->updatePage->setGPWebpayKey('TEST');
	}
}
