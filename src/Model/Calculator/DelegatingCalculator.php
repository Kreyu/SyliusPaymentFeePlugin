<?php

declare(strict_types=1);

namespace Kreyu\Sylius\PaymentFeePlugin\Model\Calculator;

use Kreyu\Sylius\PaymentFeePlugin\Exception\UndefinedPaymentMethodException;
use Kreyu\Sylius\PaymentFeePlugin\Model\PaymentMethodWithFeeInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Webmozart\Assert\Assert;

final class DelegatingCalculator implements DelegatingCalculatorInterface
{
	/** @var ServiceRegistryInterface */
	private $registry;

	public function __construct(ServiceRegistryInterface $registry)
	{
		$this->registry = $registry;
	}

	public function calculate(PaymentInterface $subject): ?int
	{
		if (null === $method = $subject->getMethod()) {
			throw new UndefinedPaymentMethodException('Cannot calculate charge for payment without a defined payment method.');
		}

		if (!$method instanceof PaymentMethodWithFeeInterface || null === $method->getCalculator()) {
			return 0;
		}

		$calculator = $this->registry->get($method->getCalculator());

		Assert::isInstanceOf($calculator, CalculatorInterface::class);

		return $calculator->calculate($subject, $method->getCalculatorConfiguration());
	}
}
