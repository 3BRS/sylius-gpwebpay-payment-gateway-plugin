<?php

declare(strict_types=1);

namespace ThreeBRS\SyliusGPWebpayPaymentGatewayPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class GPWebpayGatewayConfigurationType extends AbstractType
{
    public function __construct(private readonly array $choices)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sandbox', CheckboxType::class, [
                'label' => 'theebrs-sylius.gpwebpay_plugin.form.sandbox',
                'required' => false,
            ])
            ->add('merchantNumber', TextType::class, [
                'label' => 'theebrs-sylius.gpwebpay_plugin.form.merchantNumber',
                'constraints' => [
                    new NotBlank([
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('keyPrivatePassword', TextType::class, [
                'label' => 'theebrs-sylius.gpwebpay_plugin.form.keyPassword',
                'constraints' => [
                    new NotBlank([
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('preferredPaymentMethod', ChoiceType::class, [
                'multiple' => false,
                'expanded' => false,
                'label' => 'theebrs-sylius.gpwebpay_plugin.form.preferredPaymentMethod',
                'required' => false,
                'choices' => array_flip($this->choices),
            ])
            ->add('keyPrivate', TextareaType::class, [
                'label' => 'theebrs-sylius.gpwebpay_plugin.form.privateKey',
                'constraints' => [
                    new NotBlank([
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('allowedPaymentMethods', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'label' => 'theebrs-sylius.gpwebpay_plugin.form.allowedPaymentMethods',
                'required' => false,
                'choices' => array_flip($this->choices),
            ])
        ;
    }
}
