<?php

declare(strict_types=1);

namespace MangoSylius\SyliusGPWebpayPaymentGatewayPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class GPWebpayGatewayConfigurationType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('merchantNumber', TextType::class, [
				'label' => 'mango-sylius.gpwebpay_plugin.form.merchantNumber',
				'constraints' => [
					new NotBlank([
						'groups' => ['sylius'],
					]),
				],
			])
			->add('keyPrivateName', TextType::class, [
				'label' => 'mango-sylius.gpwebpay_plugin.form.keyName',
				'constraints' => [
					new NotBlank([
						'groups' => ['sylius'],
					]),
				],
			])
			->add('keyPrivatePassword', TextType::class, [
				'label' => 'mango-sylius.gpwebpay_plugin.form.keyPassword',
				'constraints' => [
					new NotBlank([
						'groups' => ['sylius'],
					]),
				],
			])
			->add('sandbox', CheckboxType::class, [
				'label' => 'mango-sylius.gpwebpay_plugin.form.sandbox',
			]);
	}
}
