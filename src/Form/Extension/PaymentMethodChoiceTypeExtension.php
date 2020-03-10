<?php

declare(strict_types=1);

namespace Kreyu\SyliusPaymentFeePlugin\Form\Extension;

use Kreyu\SyliusPaymentFeePlugin\Model\Calculator\CalculatorInterface;
use Kreyu\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface;
use Sylius\Bundle\PaymentBundle\Form\Type\PaymentMethodChoiceType;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Webmozart\Assert\Assert;

final class PaymentMethodChoiceTypeExtension extends AbstractTypeExtension
{
	/** @var ServiceRegistryInterface */
	private $calculatorRegistry;

	public function __construct(ServiceRegistryInterface $calculatorRegistry)
	{
		$this->calculatorRegistry = $calculatorRegistry;
	}

	public function buildView(FormView $view, FormInterface $form, array $options): void
	{
		if (!isset($options['subject'])) {
			return;
		}

		$paymentCosts = [];

		foreach ($view->vars['choices'] as $choiceView) {
			$method = $choiceView->data;

			if (!$method instanceof PaymentMethodWithFeeInterface) {
				throw new UnexpectedTypeException($method, PaymentMethodWithFeeInterface::class);
			}

			if (null === $method->getCalculator()) {
				$paymentCosts[$choiceView->value] = 0;

				continue;
			}

			$calculator = $this->calculatorRegistry->get($method->getCalculator());

			Assert::isInstanceOf($calculator, CalculatorInterface::class);

			$paymentCosts[$choiceView->value] = $calculator->calculate(
				$options['subject'],
				$method->getCalculatorConfiguration()
			);
		}

		$view->vars['payment_costs'] = $paymentCosts;
	}

	public function getExtendedType()
	{
		return PaymentMethodChoiceType::class;
	}

	public static function getExtendedTypes(): iterable
	{
		return [
			PaymentMethodChoiceType::class
		];
	}
}
