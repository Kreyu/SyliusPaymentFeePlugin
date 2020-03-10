<?php

declare(strict_types=1);

namespace Kreyu\SyliusPaymentFeePlugin\Model;

use Sylius\Component\Order\Model\AdjustableInterface;

interface OrderWithPaymentTotalInterface extends AdjustableInterface
{
	public function getPaymentTotal(): int;
}
