<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Behat\Pages\Admin\PaymentMethod\EditPageInterface;

final readonly class ManagingPaymentMethodsContext implements Context
{
    public function __construct(
        private EditPageInterface $updatePage,
    ) {
    }

    /**
     * @When I configure it with test GP webpay credentials
     */
    public function iConfigureItWithTestGPWebpayCredentials(): void
    {
        $this->updatePage->setGPWebpayMerchantNumber('TEST');
        $this->updatePage->setGPWebpayKeyPassword('TEST');
        $this->updatePage->setGPWebpayKey('TEST');
    }
}
