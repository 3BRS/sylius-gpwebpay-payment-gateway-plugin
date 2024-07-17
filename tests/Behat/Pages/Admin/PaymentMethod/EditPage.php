<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Behat\Pages\Admin\PaymentMethod;

use Sylius\Behat\Page\Admin\Channel\UpdatePage as BaseUpdatePage;

final class EditPage extends BaseUpdatePage implements EditPageInterface
{
    public function setGPWebpayMerchantNumber(string $value): void
    {
        $this->getDocument()->fillField('sylius_payment_method_gatewayConfig_config_merchantNumber', $value);
    }

    public function setGPWebpayKeyPassword(string $value): void
    {
        $this->getDocument()->fillField('sylius_payment_method_gatewayConfig_config_keyPrivatePassword', $value);
    }

    public function setGPWebpayKey(string $value): void
    {
        $this->getDocument()->fillField('sylius_payment_method_gatewayConfig_config_keyPrivate', $value);
    }
}
