<?php

declare(strict_types=1);

namespace Kreyu\SyliusPaymentFeePlugin\Form\Type\Calculator;

use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

final class FlatRateConfigurationType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('amount', MoneyType::class, [
				'label' => 'kreyu_sylius_payment_fee_plugin.form.payment_calculator.flat_rate_configuration.amount',
				'constraints' => [
					new NotBlank([
						'groups' => ['sylius']
					]),
					new Type([
						'type' => 'integer',
						'groups' => ['sylius']
					]),
				],
				'currency' => $options['currency'],
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver
			->setDefaults([
				'data_class' => null,
			])
			->setRequired('currency')
			->setAllowedTypes('currency', 'string');
	}

	public function getBlockPrefix(): string
	{
		return 'kreyu_payment_fee_payment_calculator_flat_rate';
	}
}
