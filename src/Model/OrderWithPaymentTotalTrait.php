<?php

declare(strict_types=1);

namespace Kreyu\SyliusPaymentFeePlugin\Model;

trait OrderWithPaymentTotalTrait
{
	/**
	 * Returns payment method fee together with taxes.
	 *
	 * {@inheritDoc}
	 */
	public function getPaymentTotal(): int
	{
		$paymentTotal = $this->getAdjustmentsTotal(AdjustmentInterface::PAYMENT_ADJUSTMENT);
		$paymentTotal += $this->getAdjustmentsTotal(AdjustmentInterface::TAX_ADJUSTMENT);

		return $paymentTotal;
	}

	public abstract function getAdjustmentsTotal(?string $type = null): int;
}
