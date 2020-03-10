<?php

declare(strict_types=1);

namespace Kreyu\SyliusPaymentFeePlugin\Taxation\Applicator;

use Kreyu\SyliusPaymentFeePlugin\Model\AdjustmentInterface;
use Kreyu\SyliusPaymentFeePlugin\Model\PaymentMethodWithFeeInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Taxation\Applicator\OrderTaxesApplicatorInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Taxation\Calculator\CalculatorInterface;
use Sylius\Component\Taxation\Resolver\TaxRateResolverInterface;
use Webmozart\Assert\Assert;

class OrderPaymentTaxesApplicator implements OrderTaxesApplicatorInterface
{
	/** @var CalculatorInterface */
	private $calculator;

	/** @var AdjustmentFactoryInterface */
	private $adjustmentFactory;

	/** @var TaxRateResolverInterface */
	private $taxRateResolver;

	public function __construct(
		CalculatorInterface $calculator,
		AdjustmentFactoryInterface $adjustmentFactory,
		TaxRateResolverInterface $taxRateResolver
	) {
		$this->calculator = $calculator;
		$this->adjustmentFactory = $adjustmentFactory;
		$this->taxRateResolver = $taxRateResolver;
	}

	public function apply(OrderInterface $order, ZoneInterface $zone): void
	{
		$paymentFees = $order->getAdjustmentsRecursively(AdjustmentInterface::PAYMENT_ADJUSTMENT);

		if (false === $paymentFee = $paymentFees->first()) {
			return;
		}

		if (false === $payment = $order->getPayments()->first()) {
			return;
		}

		if (null === $paymentMethod = $payment->getMethod()) {
			return;
		}

		Assert::isInstanceOf($paymentMethod, PaymentMethodWithFeeInterface::class);

		if (null === $taxRate = $this->taxRateResolver->resolve($paymentMethod, ['zone' => $zone])) {
			return;
		}

		if (0.00 === $taxAmount = $this->calculator->calculate($paymentFee->getAmount(), $taxRate)) {
			return;
		}

		$label = $taxRate->getLabel() ?? 'Payment method fee tax';

		$paymentTaxAdjustment = $this->adjustmentFactory->createWithData(
			AdjustmentInterface::TAX_ADJUSTMENT,
			$label,
			(int) $taxAmount,
			$taxRate->isIncludedInPrice()
		);

		$order->addAdjustment($paymentTaxAdjustment);
	}
}
