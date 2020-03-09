<?php

declare(strict_types=1);

namespace Kreyu\Sylius\PaymentFeePlugin\Model\Calculator;

use Sylius\Component\Core\Model\PaymentInterface;

interface CalculatorInterface
{
	public function calculate(PaymentInterface $subject, array $configuration): ?int;

	public function getType(): string;
}
