<?php

declare(strict_types=1);

namespace Kreyu\Sylius\PaymentFeePlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CalculatorChoiceType extends AbstractType
{
	/** @var array */
	private $calculators;

	public function __construct(array $calculators)
	{
		$this->calculators = $calculators;
	}

	/**
	 * {@inheritDoc}
	 */
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver
			->setDefaults([
				'choices' => array_flip($this->calculators),
			])
		;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParent(): string
	{
		return ChoiceType::class;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBlockPrefix(): string
	{
		return 'kreyu_payment_fee_payment_calculator_choice';
	}
}
