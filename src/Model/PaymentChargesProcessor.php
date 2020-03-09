<?php

declare(strict_types=1);

namespace Kreyu\Sylius\PaymentFeePlugin\Model;

use Kreyu\Sylius\PaymentFeePlugin\Model\Calculator\DelegatingCalculatorInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\AdjustmentInterface as BaseAdjustmentInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final class PaymentChargesProcessor implements OrderProcessorInterface
{
	/** @var FactoryInterface */
	private $adjustmentFactory;

	/** @var DelegatingCalculatorInterface */
	private $paymentChargesCalculator;

	public function __construct(
		FactoryInterface $adjustmentFactory,
		DelegatingCalculatorInterface $paymentChargesCalculator
	) {
		$this->adjustmentFactory = $adjustmentFactory;
		$this->paymentChargesCalculator = $paymentChargesCalculator;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param OrderInterface $order
	 */
	public function process(BaseOrderInterface $order): void
	{
		Assert::isInstanceOf($order, OrderInterface::class);

		$order->removeAdjustments(AdjustmentInterface::PAYMENT_ADJUSTMENT);

		foreach ($order->getPayments() as $payment) {
			$paymentCharge = $this->paymentChargesCalculator->calculate($payment);

			if (null === $paymentCharge) {
				continue;
			}

			/** @var BaseAdjustmentInterface $adjustment */
			$adjustment = $this->adjustmentFactory->createNew();

			Assert::isInstanceOf($adjustment, BaseAdjustmentInterface::class);

			$adjustment->setType(AdjustmentInterface::PAYMENT_ADJUSTMENT);
			$adjustment->setAmount($paymentCharge);
			$adjustment->setLabel($payment->getMethod() ? $payment->getMethod()->getName() : null);
			$adjustment->setOriginCode($payment->getMethod() ? $payment->getMethod()->getCode() : null);
			$adjustment->setNeutral(false);

			$order->addAdjustment($adjustment);
		}
	}
}
